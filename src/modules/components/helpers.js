export function appendOption(selectElement, value, text) {
  const option = document.createElement("option");
  option.value = value;
  option.textContent = text;
  selectElement.appendChild(option);
}

export function showError(message, elementSelector) {
  const element = document.querySelector(elementSelector);
  element.textContent = message;
  element.style.color = "red";
}
