// Functie simpla de confirmare pentru actiunea de stergere
function confirmaStergerea() {
    if(confirm("Ești sigur că vrei să ștergi această înregistrare? Acțiunea este ireversibilă!")) {
        alert("Acțiune simulată: Student șters! (Aici va interveni PHP-ul mai târziu)");
    }
}

// Initializare Grafic (Cerinta: realizarea de diagrame/grafice)
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('graficPromovabilitate').getContext('2d');
    
    // Un grafic de test cu note statice
    const myChart = new Chart(ctx, {
        type: 'bar', // tipul graficului: bar, line, pie etc.
        data: {
            labels: ['Note sub 5', 'Note 5-6', 'Note 7-8', 'Note 9-10'],
            datasets: [{
                label: 'Număr de studenți pe tranșe de note (Mockup)',
                data: [12, 19, 35, 24], // Date statice
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)', // Rosu pt restante
                    'rgba(255, 206, 86, 0.5)', // Galben
                    'rgba(54, 162, 235, 0.5)', // Albastru
                    'rgba(75, 192, 192, 0.5)'  // Verde pt note maxime
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});