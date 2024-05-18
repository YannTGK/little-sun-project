document.addEventListener('DOMContentLoaded', (event) => {
    const usernameInput = document.getElementById('usernameInput');
    const tableBody = document.getElementById('workersTableBody');
    const tableRows = tableBody.getElementsByTagName('tr');

    usernameInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase();

        for (let i = 0; i < tableRows.length; i++) {
            const usernameCell = tableRows[i].getElementsByTagName('td')[1];
            if (usernameCell) {
                const username = usernameCell.textContent || usernameCell.innerText;
                if (username.toLowerCase().indexOf(filter) > -1) {
                    tableRows[i].style.display = "";
                } else {
                    tableRows[i].style.display = "none";
                }
            }
        }
    });
});