document.addEventListener("DOMContentLoaded", function () {
  console.log("beranda.js loaded");

  const menuItems = document.querySelectorAll(".tab .list-item");
  const contentSections = document.querySelectorAll("#content > div");
  const hamburger = document.querySelector(".hamburger");
  const sidebar = document.querySelector(".sidebar");

  if (!menuItems.length || !contentSections.length) {
    console.error("Error: Menu items or content sections not found");
  } else {
    console.log("Menu items found:", menuItems.length);
    console.log("Content sections found:", contentSections.length);

    menuItems.forEach((item) => {
      item.addEventListener("click", function (e) {
        const target = this.getAttribute("data-target");
        if (!target) return;

        e.preventDefault();
        console.log("Clicked item with data-target:", target);

        menuItems.forEach((i) => i.classList.remove("active"));
        this.classList.add("active");

        contentSections.forEach((section) => {
          section.style.opacity = "0";
          section.style.display = "none";
          if (section.id === `${target}-content`) {
            section.style.display = "block";
            setTimeout(() => (section.style.opacity = "1"), 10);
          }
        });

        if (sidebar.classList.contains("active")) {
          sidebar.classList.remove("active");
        }
      });
    });
  }

  if (hamburger) {
    hamburger.addEventListener("click", () => {
      sidebar.classList.toggle("active");
    });
  }

  setupSidebarAjax();
  setupDeleteCampaign();
  setupEscrowFormHandler();
  // setupDetailKampanyeAjax();
});

function setupSidebarAjax() {
  const menuLinks = document.querySelectorAll(".tab .list-item a");
  menuLinks.forEach((link) => {
    link.removeEventListener("click", link._borsmenSidebarHandler);
    link._borsmenSidebarHandler = function (e) {
      const href = link.getAttribute("href");
      if (href.endsWith(".php") && !href.includes("api.php?action=logout")) {
        e.preventDefault();
        fetch(href)
          .then((res) => res.text())
          .then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newContent = doc.getElementById("content");
            if (newContent) {
              document.getElementById("content").innerHTML =
                newContent.innerHTML;
              setupSidebarAjax();
              setupDeleteCampaign();
              setupEscrowFormHandler();
              setupDetailKampanyeAjax();

              if (href.includes("analitik.php")) {
                runChartScriptIfAnalitikLoaded(html);
              }
            }
          })
          .catch((err) => console.error("Error fetching content:", err));
      }
    };
    link.addEventListener("click", link._borsmenSidebarHandler);
  });
}

function setupEscrowFormHandler() {
  const form = document.querySelector("#content form[action], #content form");
  if (form && form.querySelector('input[name="jumlah"]')) {
    form.addEventListener(
      "submit",
      function (e) {
        e.preventDefault();
        const formData = new FormData(form);
        fetch("escrow.php", {
          method: "POST",
          body: formData,
        })
          .then((res) => res.text())
          .then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newContent = doc.getElementById("content");
            if (newContent) {
              document.getElementById("content").innerHTML =
                newContent.innerHTML;
              setupSidebarAjax();
              setupEscrowFormHandler();
            }
          })
          .catch((err) => alert("Gagal setor dana: " + err));
      },
      { once: true }
    );
  }
}

function setupDeleteCampaign() {
  document.querySelectorAll(".delete-campaign").forEach((button) => {
    button.removeEventListener("click", button._borsmenDeleteHandler);
    button._borsmenDeleteHandler = function (e) {
      e.preventDefault();
      if (confirm("Apakah Anda yakin ingin menghapus kampanye ini?")) {
        const campaignId = this.getAttribute("data-id");
        fetch("delete_kampanye.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id_campaign=${campaignId}`,
        })
          .then((res) => res.json())
          .then((data) => {
            alert(data.message);
            if (data.status === 200) {
              window.location.reload();
            }
          })
          .catch((err) => console.error("Error deleting campaign:", err));
      }
    };
    button.addEventListener("click", button._borsmenDeleteHandler);
  });
}

// function setupDetailKampanyeAjax() {
//   document.querySelectorAll(".btn-detail-kampanye").forEach((link) => {
//     link.removeEventListener("click", link._borsmenDetailHandler);
//     link._borsmenDetailHandler = function (e) {
//       e.preventDefault();
//       const url = this.getAttribute("href");
//       fetch(url)
//         .then((res) => res.text())
//         .then((html) => {
//           const parser = new DOMParser();
//           const doc = parser.parseFromString(html, "text/html");
//           const newContent = doc.getElementById("content");
//           if (newContent) {
//             document.getElementById("content").innerHTML = newContent.innerHTML;
//             setupSidebarAjax();
//             setupDeleteCampaign();
//             setupEscrowFormHandler();
//             setupDetailKampanyeAjax();
//           }
//         })
//         .catch((err) => console.error("Error fetching detail:", err));
//     };
//     link.addEventListener("click", link._borsmenDetailHandler);
//   });
// }

function runChartScriptIfAnalitikLoaded(html) {
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, "text/html");
  doc.querySelectorAll("script").forEach((s) => {
    if (
      s.textContent.includes("Chart(") ||
      s.textContent.includes("new Chart(")
    ) {
      setTimeout(() => {
        eval(s.textContent);
      }, 0);
    }
  });
}
