
// Función para validar la contraseña
function validarPassword(password) {
  const validaciones = [
      { regex: /.{8,}/, mensaje: "Al menos 8 caracteres" },
      { regex: /[A-Z]/, mensaje: "Una letra mayúscula" },
      { regex: /[a-z]/, mensaje: "Una letra minúscula" },
      { regex: /\d/, mensaje: "Un número" },
      { regex: /[!@#$%^&*()_+\-=\[\]{};:,.<>?]/, mensaje: "Un carácter especial" },
      { regex: /^[^\s]+$/, mensaje: "No debe contener espacios en blanco" },
      { regex: /^(?!.*(.)\1{2})/, mensaje: "No debe tener caracteres repetidos más de dos veces seguidas" }
  ];

  const patronesComunes = ['123', '456', '789', 'abc', 'qwerty', 'password', 'admin', 'user'];

  const requisitos = [];
  validaciones.forEach(validacion => {
      if (!validacion.regex.test(password)) {
          requisitos.push(validacion.mensaje);
      }
  });

  if (patronesComunes.some(patron => password.toLowerCase().includes(patron))) {
      requisitos.push("No debe contener secuencias comunes (123, abc, qwerty, etc.)");
  }

  if (password.length > 50) {
      requisitos.push("No debe exceder los 50 caracteres");
  }

  return {
      isValid: requisitos.length === 0,
      requisitos: requisitos
  };
}

window.validarPassword = validarPassword;
