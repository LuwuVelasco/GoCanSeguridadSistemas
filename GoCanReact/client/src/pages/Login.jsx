import { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import Swal from 'sweetalert2';
import './Login.css';

function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [captchaToken, setCaptchaToken] = useState('');
  const { login, user } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    // Redirigir si ya está autenticado
    if (user) {
      redirectByRole(user.rol);
    }

    // Cargar animación de fondo
    loadBackgroundAnimation();

    // Inicializar reCAPTCHA
    if (window.grecaptcha) {
      window.grecaptcha.ready(() => {
        window.grecaptcha.render('recaptcha-container', {
          sitekey: '6Ldn970qAAAAALLJMwj-HE02sUaq1lN0JBfDPYjz',
          callback: (token) => setCaptchaToken(token)
        });
      });
    }
  }, [user]);

  const redirectByRole = (rol) => {
    switch (rol) {
      case 'Cliente':
        navigate('/cliente');
        break;
      case 'Doctor':
        navigate('/doctor');
        break;
      case 'Administrador':
        navigate('/admin');
        break;
      default:
        navigate('/');
    }
  };

  const loadBackgroundAnimation = () => {
    const canvas = document.getElementById('bg-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const particles = [];
    const particleCount = 100;

    for (let i = 0; i < particleCount; i++) {
      particles.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        radius: Math.random() * 2 + 1,
        vx: Math.random() * 2 - 1,
        vy: Math.random() * 2 - 1
      });
    }

    function animate() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.fillStyle = 'rgba(105, 48, 195, 0.5)';

      particles.forEach((particle) => {
        particle.x += particle.vx;
        particle.y += particle.vy;

        if (particle.x < 0 || particle.x > canvas.width) particle.vx *= -1;
        if (particle.y < 0 || particle.y > canvas.height) particle.vy *= -1;

        ctx.beginPath();
        ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
        ctx.fill();
      });

      requestAnimationFrame(animate);
    }

    animate();
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!email || !password) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Por favor complete todos los campos'
      });
      return;
    }

    // Validar CAPTCHA (Dp GC-43, Dom GC-50)
    if (!captchaToken) {
      Swal.fire({
        icon: 'warning',
        title: 'Verificación requerida',
        text: 'Debe completar la verificación CAPTCHA para iniciar sesión'
      });
      return;
    }

    const result = await login(email, password, captchaToken);

    if (result.success) {
      // No mostrar el mensaje de bienvenida si se requiere cambio de contraseña
      if (!result.requirePasswordChange) {
        Swal.fire({
          icon: 'success',
          title: '¡Bienvenido!',
          text: 'Inicio de sesión exitoso',
          timer: 1500,
          showConfirmButton: false
        });
      }
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: result.mensaje || 'Credenciales incorrectas'
      });
      
      // Resetear reCAPTCHA
      if (window.grecaptcha) {
        window.grecaptcha.reset();
      }
    }
  };

  const handleForgotPassword = () => {
    Swal.fire({
      title: 'Recuperar Contraseña',
      html: '<input type="email" id="forgot-email" class="swal2-input" placeholder="Correo electrónico">',
      showCancelButton: true,
      confirmButtonText: 'Enviar Código',
      cancelButtonText: 'Cancelar',
      preConfirm: () => {
        const email = document.getElementById('forgot-email').value;
        if (!email) {
          Swal.showValidationMessage('Por favor ingrese su correo');
        }
        return email;
      }
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          icon: 'info',
          title: 'Funcionalidad en desarrollo',
          text: 'El sistema de recuperación de contraseña estará disponible próximamente'
        });
      }
    });
  };

  return (
    <div className="login-page">
      <canvas id="bg-canvas"></canvas>
      
      <div className="login-container">
        <form onSubmit={handleSubmit} className="login-form">
          <h1>Inicio de Sesión</h1>

          <div className="input-box">
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="Email"
              required
            />
          </div>

          <div className="input-box">
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Contraseña"
              required
            />
          </div>

          <div className="remember-forgot">
            <label>
              <input type="checkbox" /> Recuérdame
            </label>
            <a href="#" onClick={(e) => { e.preventDefault(); handleForgotPassword(); }}>
              ¿Olvidó la contraseña?
            </a>
          </div>

          <div className="recaptcha-box">
            <div id="recaptcha-container"></div>
          </div>

          <button type="submit" className="btn btn-login">
            Ingresar
          </button>

          <div className="register-link">
            <p>
              ¿No tiene cuenta? <Link to="/registro">Regístrese</Link>
            </p>
          </div>
        </form>
      </div>
    </div>
  );
}

export default Login;
