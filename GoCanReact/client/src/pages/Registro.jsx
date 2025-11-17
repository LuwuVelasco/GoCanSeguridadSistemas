import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import Swal from 'sweetalert2';
import './Login.css';

function Registro() {
  const [formData, setFormData] = useState({
    nombre: '',
    email: '',
    password: '',
    confirmPassword: ''
  });
  const { registro } = useAuth();
  const navigate = useNavigate();

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    const { nombre, email, password, confirmPassword } = formData;

    if (!nombre || !email || !password || !confirmPassword) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Por favor complete todos los campos'
      });
      return;
    }

    // Validar dominio de email (Dp GC-44)
    const dominiosValidos = ['@gmail.com', '@hotmail.com', '@outlook.com', '@yahoo.com', '@live.com'];
    const tieneDominioValido = dominiosValidos.some(dominio => email.toLowerCase().endsWith(dominio));
    
    if (!tieneDominioValido) {
      Swal.fire({
        icon: 'error',
        title: 'Correo no válido',
        html: 'El correo debe pertenecer a un dominio válido como:<br><strong>@gmail.com, @hotmail.com, @outlook.com</strong>'
      });
      return;
    }

    if (password !== confirmPassword) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Las contraseñas no coinciden'
      });
      return;
    }

    // Validar contraseña segura (Dp GC-46)
    const secuenciasProhibidas = ['123', '234', '345', '456', '567', '678', '789', 'abc', 'bcd', 'cde', 'qwerty', '000', '111', '222'];
    const tieneSecuencia = secuenciasProhibidas.some(seq => password.toLowerCase().includes(seq));
    
    if (tieneSecuencia) {
      Swal.fire({
        icon: 'error',
        title: 'Contraseña no válida',
        text: 'La contraseña no debe contener secuencias comunes como 123, abc, qwerty, etc.'
      });
      return;
    }

    if (password.length < 6) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'La contraseña debe tener al menos 6 caracteres'
      });
      return;
    }

    const result = await registro(email, nombre, password, true);

    if (result.success) {
      Swal.fire({
        icon: 'success',
        title: '¡Registro exitoso!',
        text: 'Ahora puede iniciar sesión',
        timer: 2000,
        showConfirmButton: false
      }).then(() => {
        navigate('/login');
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: result.mensaje || 'Error al registrar usuario'
      });
    }
  };

  return (
    <div className="login-page">
      <canvas id="bg-canvas"></canvas>
      
      <div className="login-container">
        <form onSubmit={handleSubmit} className="login-form">
          <h1>Registro</h1>

          <div className="input-box">
            <input
              type="text"
              name="nombre"
              value={formData.nombre}
              onChange={handleChange}
              placeholder="Nombre completo"
              required
            />
          </div>

          <div className="input-box">
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              placeholder="Email"
              required
            />
          </div>

          <div className="input-box">
            <input
              type="password"
              name="password"
              value={formData.password}
              onChange={handleChange}
              placeholder="Contraseña"
              required
            />
          </div>

          <div className="input-box">
            <input
              type="password"
              name="confirmPassword"
              value={formData.confirmPassword}
              onChange={handleChange}
              placeholder="Confirmar contraseña"
              required
            />
          </div>

          <button type="submit" className="btn btn-login">
            Registrarse
          </button>

          <div className="register-link">
            <p>
              ¿Ya tiene cuenta? <Link to="/login">Inicie sesión</Link>
            </p>
          </div>
        </form>
      </div>
    </div>
  );
}

export default Registro;
