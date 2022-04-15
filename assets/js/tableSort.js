function sortTable() {

    let table, rows, switching, i, x, y, shouldSwitch;
    table = document.getElementById("vulnTable");
    switching = true;

    console.log("Running")

    while (switching) {
        // Start by saying: no switching is done:
        switching = false;
        rows = table.rows;
        /* Loop through all table rows (except the
        first, which contains table headers): */
        for (i = 1; i < (rows.length - 1); i++) {

            // Start by saying there should be no switching:
            shouldSwitch = false;
            /* Get the two elements you want to compare,
            one from current row and one from the next: */
            x = rows[i].innerHTML;
            y = rows[i + 1].getElementsByTagName("tr")[0];
            console.log(x)
            // Check if the two rows should switch place:
            if (x.innerText.toLowerCase() > y.innerText.toLowerCase()) {
                // If so, mark as a switch and break the loop:
                shouldSwitch = true;
                break;
            }
        }
        if (shouldSwitch) {
            /* If a switch has been marked, make the switch
            and mark that a switch has been done: */
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
        }
    }
}