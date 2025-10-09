self.addEventListener("install", (event) => {
  console.log("Service Worker installing.");
});

// Load Navbar
fetch("navbar.html")
  .then((res) => res.text())
  .then((data) => (document.getElementById("navbar").innerHTML = data));

// Load Footer
fetch("footer.html")
  .then((res) => res.text())
  .then((data) => (document.getElementById("footer").innerHTML = data));
