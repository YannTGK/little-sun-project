function applyFilters() {
    var usernameFilter, hubnameFilter, taskTypeFilter, table, tr, td, i, usernameValue, hubnameValue, taskTypeValue;
    usernameFilter = document.getElementById("usernameInput").value.toUpperCase();
    hubnameFilter = document.getElementById("hubnameInput").value.toUpperCase();
    taskTypeFilter = document.getElementById("taskTypeInput").value.toUpperCase();
    table = document.getElementById("workersTableBody");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        tdUsername = tr[i].getElementsByTagName("td")[1];
        tdHubname = tr[i].getElementsByTagName("td")[5]; 
        tdTaskType = tr[i].getElementsByTagName("td")[6]; 
        if (tdUsername && tdHubname && tdTaskType) {
            usernameValue = tdUsername.textContent || tdUsername.innerText;
            hubnameValue = tdHubname.textContent || tdHubname.innerText;
            taskTypeValue = tdTaskType.textContent || tdTaskType.innerText;
            if (usernameValue.toUpperCase().indexOf(usernameFilter) > -1 &&
                hubnameValue.toUpperCase().indexOf(hubnameFilter) > -1 &&
                taskTypeValue.toUpperCase().indexOf(taskTypeFilter) > -1) {
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