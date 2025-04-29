document.addEventListener("DOMContentLoaded", function () {
    const menuItems = document.querySelectorAll(".tab .list-item");
    const contentDiv = document.getElementById("content");

    menuItems.forEach(item => {
        item.addEventListener("click", function () {
            let target = this.getAttribute("data-target");

            if (!target) return;

            fetch(target)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Failed to load content");
                    }
                    return response.text();
                })
                .then(data => {
                    contentDiv.innerHTML = data;
                })
                .catch(error => {
                    contentDiv.innerHTML = "<p>Error loading content.</p>";
                    console.error(error);
                });
        });
    });
});