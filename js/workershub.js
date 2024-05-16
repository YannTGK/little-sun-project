function applyFilters() {
    var usernameFilter, hubnameFilter, taskTypeFilter, dayFilter, monthFilter, yearFilter, table, tr, td, i, usernameValue, hubnameValue, taskTypeValue, dateValue;
    usernameFilter = document.getElementById("usernameInput").value.toUpperCase();
    hubnameFilter = document.getElementById("hubnameInput").value.toUpperCase();
    taskTypeFilter = document.getElementById("taskTypeInput").value.toUpperCase();
    dayFilter = document.getElementById("dayInput").value;
    monthFilter = document.getElementById("monthInput").value;
    yearFilter = document.getElementById("yearInput").value;
    table = document.getElementById("workersTableBody");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        tdUsername = tr[i].getElementsByTagName("td")[1];
        tdHubname = tr[i].getElementsByTagName("td")[5]; 
        tdTaskType = tr[i].getElementsByTagName("td")[6]; 
        tdDate = tr[i].getElementsByTagName("td")[9]; // assuming date is in the 10th column
        if (tdUsername && tdHubname && tdTaskType && tdDate) {
            usernameValue = tdUsername.textContent || tdUsername.innerText;
            hubnameValue = tdHubname.textContent || tdHubname.innerText;
            taskTypeValue = tdTaskType.textContent || tdTaskType.innerText;
            dateValue = tdDate.textContent || tdDate.innerText;

            // Split the date value to extract day, month and year
            var parts = dateValue.split("-");
            var day = parts[2];
            var month = parts[1];
            var year = parts[0];

            if (usernameValue.toUpperCase().indexOf(usernameFilter) > -1 &&
                hubnameValue.toUpperCase().indexOf(hubnameFilter) > -1 &&
                taskTypeValue.toUpperCase().indexOf(taskTypeFilter) > -1 &&
                (dayFilter === "" || day === dayFilter) &&
                (monthFilter === "" || month === monthFilter) &&
                (yearFilter === "" || year === yearFilter)) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

document.getElementById("usernameInput").addEventListener("input", applyFilters);
document.getElementById("hubnameInput").addEventListener("input", applyFilters);
document.getElementById("taskTypeInput").addEventListener("input", applyFilters);
document.getElementById("dayInput").addEventListener("input", applyFilters);
document.getElementById("monthInput").addEventListener("input", applyFilters);
document.getElementById("yearInput").addEventListener("input", applyFilters);
