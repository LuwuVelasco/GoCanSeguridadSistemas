import { Link } from 'react-router-dom';
import './Home.css';

function Home() {
  return (
    <div className="home-page">
      {/* Header */}
      <header className="header">
        <div className="container">
          <Link to="/" className="logo">GoCan</Link>
          
          <nav className="navbar">
            <ul className="navbar-list">
              <li><a href="#home">Inicio</a></li>
              <li><a href="#servicios">Servicios</a></li>
              <li><a href="#catalogo">Catálogo</a></li>
              <li><a href="#contacto">Contacto</a></li>
            </ul>
          </nav>

          <div className="header-actions">
            <Link to="/login" className="btn btn-primary">Iniciar Sesión</Link>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="hero" id="home">
        <div className="container">
          <h1 className="hero-title">
            <span>Atención</span> De Alta Calidad
          </h1>
          <p className="hero-text">Cuidamos a los que más nos quieren</p>
          <Link to="/registro" className="btn btn-secondary">Comenzar</Link>
        </div>
      </section>

      {/* Servicios */}
      <section className="servicios" id="servicios">
        <div className="container">
          <h2 className="section-title">
            <span>Ofrecemos</span> servicios como
          </h2>

          <div className="servicios-grid">
            <div className="servicio-card">
              <div className="servicio-icon">💉</div>
              <h3>Vacunas</h3>
              <p>Protección completa para tu mascota</p>
            </div>

            <div className="servicio-card">
              <div className="servicio-icon">✂️</div>
              <h3>Peluquería</h3>
              <p>Cuidado estético profesional</p>
            </div>

            <div className="servicio-card">
              <div className="servicio-icon">🦷</div>
              <h3>Odontología</h3>
              <p>Salud dental para mascotas</p>
            </div>

            <div className="servicio-card">
              <div className="servicio-icon">💊</div>
              <h3>Desparasitación</h3>
              <p>Prevención y tratamiento</p>
            </div>

            <div className="servicio-card">
              <div className="servicio-icon">🏥</div>
              <h3>Atención 24/7</h3>
              <p>Siempre disponibles para ti</p>
            </div>

            <div className="servicio-card">
              <div className="servicio-icon">🔬</div>
              <h3>Laboratorio</h3>
              <p>Análisis y diagnósticos</p>
            </div>
          </div>
        </div>
      </section>

      {/* Ofertas */}
      <section className="ofertas">
        <div className="container">
          <h2 className="section-title">
            <span>Ofertas</span> especiales
          </h2>

          <div className="ofertas-grid">
            <div className="oferta-card">
              <h3>2x1 en vacuna triplefelina</h3>
              <p>Aprovecha esta oferta especial</p>
            </div>

            <div className="oferta-card">
              <h3>Requisitos para viajar</h3>
              <p>Conoce todo lo necesario para viajar con tu mascota</p>
            </div>

            <div className="oferta-card">
              <h3>Vacunación de conejos</h3>
              <p>Ahora también vacunamos a tus conejos</p>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="footer">
        <div className="container">
          <div className="footer-content">
            <div className="footer-section">
              <h3>GoCan</h3>
              <p>Centro Integral Veterinario</p>
            </div>

            <div className="footer-section">
              <h4>Contacto</h4>
              <p>Email: info@gocan.com</p>
              <p>Teléfono: (123) 456-7890</p>
            </div>

            <div className="footer-section">
              <h4>Horarios</h4>
              <p>Lunes - Viernes: 8:00 - 20:00</p>
              <p>Sábados: 9:00 - 18:00</p>
              <p>Domingos: 10:00 - 14:00</p>
            </div>
          </div>

          <div className="footer-bottom">
            <p>&copy; 2025 GoCan. Todos los derechos reservados.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}

export default Home;
