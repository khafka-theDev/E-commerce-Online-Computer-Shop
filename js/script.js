document.addEventListener("DOMContentLoaded", () => {
  const mobileMenuIcon = document.querySelector('#mobile i'); // Hamburger icon
  const navbar = document.querySelector('#navbar'); // Navigation bar
  const closeBtn = document.querySelector('#close'); // Close button for mobile menu

  // Open the mobile menu
  mobileMenuIcon.addEventListener('click', () => {
    navbar.classList.add('active'); // Show the navbar
    document.body.style.overflow = "hidden"; // Disable scrolling when menu is open
  });

  // Close the mobile menu
  closeBtn.addEventListener('click', () => {
    navbar.classList.remove('active'); // Hide the navbar
    document.body.style.overflow = "auto"; // Re-enable scrolling
  });

  // Close the navbar if the user clicks outside of it
  document.addEventListener('click', (e) => {
    if (!navbar.contains(e.target) && !mobileMenuIcon.contains(e.target)) {
      navbar.classList.remove('active');
      document.body.style.overflow = "auto"; // Ensure scrolling is re-enabled
    }
  });

  // Prevent clicks on the navbar from propagating and closing it
  navbar.addEventListener('click', (e) => {
    e.stopPropagation(); // Stops event from propagating to the document click listener
  });
});
