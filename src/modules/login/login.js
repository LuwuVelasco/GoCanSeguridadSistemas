// /src/modules/login/login.js
document.addEventListener('DOMContentLoaded', function () {
  // ====== Config ======
  // Si ves el HTML con Live Server, el PHP corre en Apache.
  const isLiveServer =
    (location.hostname === '127.0.0.1' || location.hostname === 'localhost') &&
    location.port === '5500';

  const API_BASE = isLiveServer
    ? 'http://localhost/GoCanSeguridadSistemas/src/modules/php'
    : '../php'; // por si alguna vez lo sirves desde Apache también

  const api = (p) => `${API_BASE}/${p}`;

  const botonIngresar = document.getElementById('ingresarBtn');
  const forgotPasswordLink = document.getElementById('forgotPassword');
  const forgotPasswordModal = document.getElementById('forgotPasswordModal');
  const resetPasswordModal = document.getElementById('resetPasswordModal');

  let intentosFallidos = 0;
  let bloqueado = false;
  let enProgreso = false;

  // ====== Helpers ======
  function bloquearBoton() {
    bloqueado = true;
    deshabilitarBoton(true);
    setTimeout(function () {
      bloqueado = false;
      intentosFallidos = 0;
      deshabilitarBoton(false);
    }, 300000); // 5 minutos
  }

  function deshabilitarBoton(disabled) {
    if (!botonIngresar) return;
    botonIngresar.disabled = disabled;
    botonIngresar.style.opacity = disabled ? '0.7' : '1';
    botonIngresar.style.pointerEvents = disabled ? 'none' : 'auto';
  }

  function resetCaptcha() {
    try { if (window.grecaptcha) grecaptcha.reset(); } catch (_) {}
  }

  function validarRecaptcha() {
    try {
      if (!window.grecaptcha) return '';
      return grecaptcha.getResponse();
    } catch {
      return '';
    }
  }

  function urlEncode(obj) {
    return Object.keys(obj)
      .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(obj[k] ?? '')}`)
      .join('&');
  }

  // ====== Login principal ======
  if (botonIngresar) {
    botonIngresar.addEventListener('click', function (event) {
      event.preventDefault();
      if (bloqueado) {
        Swal.fire({
          icon: 'error',
          title: 'Acceso bloqueado',
          text: 'Demasiados intentos. Espera 5 minutos e inténtalo de nuevo.'
        });
        return;
      }
      if (enProgreso) return;
      iniciarSesion();
    });
  } else {
    console.error('El botón de ingreso no se encontró en el DOM');
  }

  function iniciarSesion() {
    const email = (document.getElementById('email')?.value || '').trim();
    const password = document.getElementById('password')?.value || '';
    const recaptchaResponse = validarRecaptcha();

    if (!email || !password) {
      Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Ingresa email y contraseña.' });
      return;
    }

    if (!recaptchaResponse && !window.RECAPTCHA_BYPASS_LOCAL) {
      // Registrar intento en log_usuarios aunque no se envíe el login
      fetch(api('registrar_log_usuario.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: urlEncode({
          // id_usuario opcional (aún no lo conocemos)
          accion: 'captcha_fallido',
          descripcion: `Intento de login SIN reCAPTCHA para ${email || '(sin email)'}`
        })
      }).catch(() => { /* no romper la UX por el log */ });

      Swal.fire({ icon: 'warning', title: 'Verificación requerida', text: 'Resuelve el reCAPTCHA.' });
      return;
    }

    enProgreso = true;
    deshabilitarBoton(true);

    fetch(api('login.php'), {
      method: 'POST',
      credentials: 'include', // NECESARIO: Live Server (5500) -> Apache (80) = orígenes distintos
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Accept': 'application/json'
      },
      body: urlEncode({
        email,
        password,
        'g-recaptcha-response': recaptchaResponse
      })
    })
      .then(r => r.json())
      .then(data => {
        if (data?.estado === 'success') {
          localStorage.setItem('id_usuario', data.id_usuario);
          if (data.id_doctores != null) localStorage.setItem('id_doctores', data.id_doctores);

          // Verificar vencimiento/rotación de contraseña
          return fetch(api('verificar_password.php'), {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
            body: urlEncode({ id_usuario: data.id_usuario })
          })
            .then(resp => resp.json())
            .then(passwordCheck => {
              if (passwordCheck?.estado === 'expired' || passwordCheck?.estado === 'change_required') {
                const modalEl = document.getElementById('passwordExpiredModal');
                if (!modalEl) return redirigirUsuario(data);

                const expiredModal = new bootstrap.Modal(modalEl);
                expiredModal.show();

                const updateBtn = document.getElementById('updateExpiredPasswordBtn');
                if (updateBtn) {
                  updateBtn.onclick = () => {
                    const newPassword = document.getElementById('expiredNewPassword')?.value || '';
                    const validacion = validarPassword(newPassword); // de validaciones.js
                    if (!validacion?.isValid) {
                      Swal.fire({
                        icon: 'error',
                        title: 'Contraseña inválida',
                        html: `La contraseña no cumple:<br><ul>${(validacion?.requisitos || [])
                          .map(req => `<li>${req}</li>`).join('')}</ul>`
                      });
                      return;
                    }

                    fetch(api('new_password.php'), {
                      method: 'POST',
                      credentials: 'include',
                      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                      body: urlEncode({ email, new_password: newPassword })
                    })
                      .then(u => u.json())
                      .then(updateData => {
                        if (updateData?.estado === 'success') {
                          fetch(api('registrar_log_usuario.php'), {
                            method: 'POST',
                            credentials: 'include',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: urlEncode({
                              id_usuario: data.id_usuario,
                              accion: 'actualizacion_contrasena',
                              descripcion: `El usuario ${data.id_usuario} actualizó su contraseña por expiración`
                            })
                          }).catch(() => {});

                          Swal.fire({ icon: 'success', title: 'Contraseña actualizada' })
                            .then(() => {
                              expiredModal.hide();
                              redirigirUsuario(data);
                            });
                        } else {
                          Swal.fire({ icon: 'error', title: 'Error', text: updateData?.mensaje || 'No se pudo actualizar.' });
                        }
                      })
                      .catch(() => Swal.fire({ icon: 'error', title: 'Error de red', text: 'Inténtalo de nuevo.' }));
                  };
                }
              } else {
                redirigirUsuario(data);
              }
            });
        }

        // ERROR de credenciales o captcha
        resetCaptcha();
        intentosFallidos++;
        if (intentosFallidos >= 3) {
          fetch(api('registrar_log_usuario.php'), {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: urlEncode({
              accion: 'bloqueo_usuario',
              descripcion: 'Bloqueo por demasiados intentos fallidos'
            })
          }).catch(() => {});
          bloquearBoton();
        }

        Swal.fire({
          icon: 'error',
          title: 'Error de inicio de sesión',
          text: data?.mensaje || 'Credenciales inválidas'
        });
      })
      .catch(err => {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo contactar al servidor.' });
      })
      .finally(() => {
        enProgreso = false;
        if (!bloqueado) deshabilitarBoton(false);
      });
  }

  // ====== Redirecciones por rol ======
  function redirigirUsuario(data) {
    localStorage.setItem('id_usuario', data.id_usuario);

    fetch(api('obtener_rol_usuario.php'), {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
      body: urlEncode({ id_usuario: data.id_usuario })
    })
      .then(r => r.json())
      .then(rolData => {
        if (rolData?.estado === 'success') {
          localStorage.setItem('id_rol', rolData.id_rol);
          localStorage.setItem('nombre_rol', rolData.nombre_rol);

          // Rutas relativas desde /src/modules/login/
          if (rolData.nombre_rol === 'Doctor') {
            window.location.href = '../coreDoctores/indexdoctores.html';
          } else if (rolData.nombre_rol === 'Administrador') {
            window.location.href = '../coreadmin/indexadmin.html';
          } else if (rolData.nombre_rol === 'Cliente') {
            window.location.href = '../citas/citas.html';
          } else {
            window.location.href = '../coreVariable/index.html';
          }
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: rolData?.mensaje || 'No se pudo obtener el rol.' });
        }
      })
      .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al obtener el rol del usuario.' }));
  }

  // ====== Recuperación de contraseña ======
  try { emailjs.init({ publicKey: 'lxBqvP8DcEyWXTcxi' }); } catch (_) {}

  if (forgotPasswordLink) {
    forgotPasswordLink.addEventListener('click', function (e) {
      e.preventDefault();
      if (forgotPasswordModal) forgotPasswordModal.style.display = 'block';
    });
  }

  const sendVerificationCodeBtn = document.getElementById('sendVerificationCodeBtn');
  if (sendVerificationCodeBtn) {
    sendVerificationCodeBtn.addEventListener('click', function (e) {
      e.preventDefault();
      sendVerificationCode();
    });
  }

  const resetPasswordBtn = document.getElementById('resetPasswordBtn');
  if (resetPasswordBtn) {
    resetPasswordBtn.addEventListener('click', function (e) {
      e.preventDefault();
      resetPassword();
    });
  }

  const closeButtons = document.querySelectorAll('.close');
  closeButtons.forEach(btn => {
    btn.addEventListener('click', function () {
      if (forgotPasswordModal) forgotPasswordModal.style.display = 'none';
      if (resetPasswordModal) resetPasswordModal.style.display = 'none';
    });
  });

  function sendVerificationCode() {
    const email = document.getElementById('forgotEmail')?.value || '';
    if (!email) {
      Swal.fire({ icon: 'warning', title: 'Falta email', text: 'Ingresa tu correo.' });
      return;
    }

    const verificationCode = generateRandomCode();
    sessionStorage.setItem('verificationCode', verificationCode);

// ya no uses service_nhpwkm8/template_48zopgh
    emailjs.send('service_l3j8jvq', 'template_nyxmk6j', {
      to_email: email,
      verification_code: verificationCode
    })
    .then(() => {
      Swal.fire({ icon: 'success', title: 'Código enviado', text: 'Revisa tu correo.' });
      // ...
    })
    .catch(err => {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo enviar el correo.' });
    });
  }

  function resetPassword() {
    const email = document.getElementById('forgotEmail')?.value || '';
    const verificationCode = document.getElementById('verificationCode')?.value || '';
    const newPassword = document.getElementById('newPassword')?.value || '';

    if (!email || !verificationCode || !newPassword) {
      Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Completa todos los campos.' });
      return;
    }

    const expected = sessionStorage.getItem('verificationCode');
    if (verificationCode !== expected) {
      Swal.fire({ icon: 'error', title: 'Código incorrecto', text: 'El código de verificación es incorrecto.' });
      return;
    }

    const validacion = validarPassword(newPassword);
    if (!validacion?.isValid) {
      Swal.fire({
        icon: 'error',
        title: 'Contraseña inválida',
        html: `La contraseña no cumple:<br><ul>${(validacion?.requisitos || [])
          .map(req => `<li>${req}</li>`).join('')}</ul>`
      });
      return;
    }

    fetch(api('new_password.php'), {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
      body: urlEncode({ email, new_password: newPassword })
    })
      .then(r => r.json())
      .then(data => {
        if (data?.estado === 'success') {
          fetch(api('obtener_usuario_por_email.php'), {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
            body: urlEncode({ email })
          })
            .then(resp => resp.json())
            .then(userResponse => {
              if (userResponse?.estado === 'success') {
                const { id_usuario, nombre } = userResponse.data || {};
                fetch(api('registrar_log_usuario.php'), {
                  method: 'POST',
                  credentials: 'include',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: urlEncode({
                    id_usuario,
                    nombre_usuario: nombre,
                    accion: 'restablecimiento_contrasena',
                    descripcion: `Se restableció la contraseña para ${nombre} (${email})`
                  })
                }).catch(() => {});
              }
            }).catch(() => {});

          Swal.fire({ icon: 'success', title: 'Contraseña cambiada', text: 'Se cambió exitosamente.' });
          if (resetPasswordModal) resetPasswordModal.style.display = 'none';
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: data?.mensaje || 'No se pudo cambiar la contraseña.' });
        }
      })
      .catch(() => Swal.fire({ icon: 'error', title: 'Error de red', text: 'Inténtalo de nuevo.' }));
  }

  function generateRandomCode() {
    return Math.random().toString(36).substring(2, 7).toUpperCase();
  }
});
