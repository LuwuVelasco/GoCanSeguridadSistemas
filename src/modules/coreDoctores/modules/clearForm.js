// Función para limpiar los campos de un formulario
function clearForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset(); // Restablece los valores del formulario al estado inicial
    }
}

window.clearForm = clearForm;