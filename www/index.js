/** @type {HTMLInputElement} */
const fileInput = document.getElementById("file-input");

addEventListener("paste", async (ev) => {
  ev.preventDefault();

  fileInput.files = ev.clipboardData.files;
});

addEventListener("dragover", (ev) => {
  if (ev.dataTransfer.types.includes("Files")) {
    ev.preventDefault();
    ev.dataTransfer.dropEffect = "copy";
  }
});

addEventListener("drop", async (ev) => {
  ev.preventDefault();

  fileInput.files = ev.dataTransfer.files;
});
