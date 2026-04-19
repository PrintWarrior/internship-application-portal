document.addEventListener("DOMContentLoaded", function () {
    var body = document.body;
    var sidebar = document.querySelector(".sidebar");
    var topnav = document.querySelector(".topnav");
    var topnavRight = document.querySelector(".topnav-right");

    if (!sidebar && !topnavRight) {
        return;
    }

    body.classList.add("js-hamburger-ready");

    var overlay = document.createElement("div");
    overlay.className = "nav-overlay";
    body.appendChild(overlay);

    function createToggleButton() {
        var button = document.createElement("button");
        button.type = "button";
        button.className = "hamburger-toggle";
        button.setAttribute("aria-expanded", "false");
        button.setAttribute("aria-label", "Toggle navigation menu");
        button.innerHTML = "<span></span>";
        return button;
    }

    function setOpenState(isOpen) {
        body.classList.toggle("nav-open", isOpen);
        toggles.forEach(function (toggle) {
            toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        });
    }

    function toggleMenu() {
        setOpenState(!body.classList.contains("nav-open"));
    }

    var toggles = [];

    if (topnav) {
        topnav.classList.add("has-hamburger");
        var topButton = createToggleButton();
        topnav.appendChild(topButton);
        toggles.push(topButton);
    } else if (sidebar) {
        var mobileBar = document.createElement("div");
        mobileBar.className = "mobile-nav-bar";

        var title = document.createElement("div");
        title.className = "mobile-nav-title";
        title.textContent = document.title || "Menu";

        var floatingButton = createToggleButton();
        mobileBar.appendChild(title);
        mobileBar.appendChild(floatingButton);
        body.insertBefore(mobileBar, body.firstChild);
        body.classList.add("has-mobile-nav-bar");
        toggles.push(floatingButton);
    }

    toggles.forEach(function (toggle) {
        toggle.addEventListener("click", toggleMenu);
    });

    overlay.addEventListener("click", function () {
        setOpenState(false);
    });

    if (sidebar) {
        sidebar.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                if (window.innerWidth <= 768) {
                    setOpenState(false);
                }
            });
        });
    }

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            setOpenState(false);
        }
    });

    window.addEventListener("resize", function () {
        if (window.innerWidth > 768) {
            setOpenState(false);
        }
    });
});
