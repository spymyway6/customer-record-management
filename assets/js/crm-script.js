function changeTable(url, value){
    window.location.href = `${url}&selectedTable=${value}`
}
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const tableRows = document.querySelectorAll("#crmTable tbody tr");

    function filterTable() {
        const filter = searchInput.value.toLowerCase().trim();

        tableRows.forEach(row => {
            const rowData = row.textContent.toLowerCase();
            row.style.display = rowData.includes(filter) ? "" : "none";
        });
    }

    // Listen for both "keyup" and "input" (for search fields)
    searchInput.addEventListener("keyup", filterTable);
    searchInput.addEventListener("input", filterTable);
});

function chnageTools(value){
    const currToolsContent = document.getElementById(value);
    const crmTab = document.querySelectorAll(".crm-tab-content");
    crmTab.forEach(row => {
        row.classList.add("d-none");
    });
    currToolsContent.classList.remove("d-none");
}