document.getElementById('dataForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const nombre = document.getElementById('nombre').value;

    document.getElementById('result').innerText =
        "Nombre registrado: " + nombre;
});
