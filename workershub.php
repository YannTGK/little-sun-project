<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>workershub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        img {
            max-width: 100px;
            max-height: 100px;
        }
    </style>
</head>
<body>
    <h1>Werknemersgegevens</h1>
    <a href="index.php">Tijdelijke Home</a>
    <input type="text" id="usernameInput" placeholder="Filter op gebruikersnaam...">
    <input type="text" id="hubnameInput" placeholder="Filter op hubnaam...">
    <input type="text" id="taskTypeInput" placeholder="Filter op taaktype...">
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Gebruikersnaam</th>
                <th>E-mail</th>
                <th>Profielfoto</th>
                <th>Rol</th>
                <th>Hubnaam</th>
                <th>Taaktype</th>
            </tr>
        </thead>
        <tbody id="workersTableBody">
            <?php
            include_once(__DIR__ . "/classes/allworkers.php"); 

            $allWorkers = new AllWorkers(); 
            $workersData = $allWorkers->fetchWorkers(); 

            foreach ($workersData as $worker) {
                echo "<tr>";
                echo "<td>" . $worker['user_id'] . "</td>";
                echo "<td>" . $worker['username'] . "</td>";
                echo "<td>" . $worker['email'] . "</td>";
                echo "<td><img src='" . $worker['profilepicture'] . "' alt='Profielfoto'></td>";
                echo "<td>" . $worker['role'] . "</td>";
                echo "<td>" . $worker['hubname'] . "</td>";
                echo "<td>" . $worker['TaskType'] . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
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
    </script>
</body>
</html>
