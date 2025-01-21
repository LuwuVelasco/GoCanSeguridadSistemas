document.addEventListener('DOMContentLoaded', function() {
    emailjs.init("XhWMaSqNfASzICac5");
    document.getElementById('crearCuentaBtn').addEventListener('click', registrarUsuario);
    
    function validarPassword(password) {
        // Lista de validaciones ampliada y más precisa
        const validaciones = [
            { regex: /.{8,}/, mensaje: "Al menos 8 caracteres" },
            { regex: /[A-Z]/, mensaje: "Una letra mayúscula" },
            { regex: /[a-z]/, mensaje: "Una letra minúscula" },
            { regex: /\d/, mensaje: "Un número" },
            // Expresión regular más completa para caracteres especiales
            { regex: /[!@#$%^&*()_+\-=\[\]{};:,.<>?]/, mensaje: "Un carácter especial (!@#$%^&*()_+-=[];:,.<>?)" },
            // Validaciones adicionales de seguridad
            { regex: /^[^\s]+$/, mensaje: "No debe contener espacios en blanco" },
            { regex: /^(?!.*(.)\1{2})/, mensaje: "No debe tener caracteres repetidos más de dos veces seguidas" }
        ];
    
        // Lista de patrones comunes a evitar
        const patronesComunes = [
            '123', '456', '789', 'abc', 'qwerty', 'password', 'admin', 'user'
        ];
    
        // Verificar cada requisito
        const requisitos = [];
        validaciones.forEach(validacion => {
            if (!validacion.regex.test(password)) {
                requisitos.push(validacion.mensaje);
            }
        });
    
        // Verificar patrones comunes
        if (patronesComunes.some(patron => password.toLowerCase().includes(patron))) {
            requisitos.push("No debe contener secuencias comunes (123, abc, qwerty, etc.)");
        }
    
        // Verificar longitud máxima
        if (password.length > 50) {
            requisitos.push("No debe exceder los 50 caracteres");
        }
    
        return {
            isValid: requisitos.length === 0,
            requisitos: requisitos
        };
    }
    function validarEmail(email) {
        // Expresión regular para validar formato de email
        const emailRegex = /^[^\s@]+@gmail\.com$/;    
        if (!emailRegex.test(email)) {
            Swal.fire({
                title: 'Correo electrónico no válido',
                text: 'Por favor, ingrese un correo electrónico válido que contenga un "@" y un dominio correcto.',
                icon: 'error'
            });
            return false;
        }
        return true;
    }
    
    function registrarUsuario() {
        let email = document.getElementById('email').value;
        let nombre = document.getElementById('nombre').value;
        let password = document.getElementById('password').value;
        let token = generateToken();
        let cargo = true;
        //Validar email
        if (!validarEmail(email)) {
            return; // Detener el flujo si el email no es válido
        }
        // Validar contraseña
        const validacionPassword = validarPassword(password);        
        if (!validacionPassword.isValid) {
            Swal.fire({
                title: 'Contraseña no válida',
                html: 'La contraseña debe tener:<br>' + validacionPassword.requisitos.map(req => `- ${req}`).join('<br>'),
                icon: 'error'
            });
            return;
        }

        emailjs.send("service_nhpwkm8", "template_guvck1n", {
            to_email: email,
            nombre: nombre,
            token: token
        }).then(function(response) {
            console.log('Correo electrónico enviado con éxito:', response);
            promptForToken(token, email, nombre, password, cargo);
        }, function(error) {
            console.error('Error al enviar el correo electrónico:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error al enviar el correo electrónico',
                icon: 'error'
            });
        });
    }

    function promptForToken(sentToken, email, nombre, password, cargo) {
        Swal.fire({
            title: 'Verificación',
            text: 'Por favor, ingrese el token que ha sido enviado a su correo electrónico:',
            input: 'text',
            showCancelButton: true,
            confirmButtonText: 'Verificar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const userToken = result.value;
                if (userToken.trim() === sentToken.toString().trim()) {
                    fetch("http://localhost/GoCanSeguridadSistemas/src/modules/php/registro.php", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: email,
                            nombre: nombre,
                            password: password,
                            token: sentToken,
                            cargo: cargo,
                            verified: true
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.estado === "success") {
                            Swal.fire({
                                title: 'Éxito',
                                text: 'Usuario registrado correctamente. Inicie sesión para continuar.',
                                icon: 'success'
                            }).then(() => {
                                document.getElementById('email').value = '';
                                document.getElementById('nombre').value = '';
                                document.getElementById('password').value = '';
                            });
                        } else {
                            throw new Error(data.mensaje || 'Error en el registro');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: error.message || 'Hubo un error en el registro',
                            icon: 'error'
                        });
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'El token ingresado no es correcto. Intente nuevamente.',
                        icon: 'error'
                    });
                }
            }
        });
    }

    function generateToken() {
        return Math.floor(Math.random() * 900000) + 100000;
    }
});
