document.getElementById('dataForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const nombre = document.getElementById('nombre').value;

    fetch('index.php', {
        method: 'POST',
        body: new URLSearchParams({
            'nombre': nombre
        })
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('result').innerText = data;
    })
    .catch(error => console.error('Error:', error));
});
