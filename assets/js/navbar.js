// Active link toggle
    document.querySelectorAll(".nav-link").forEach(link => {
      link.addEventListener("click", function () {
        document.querySelectorAll(".nav-link").forEach(l => l.classList.remove("active"));
        this.classList.add("active");
      });
    });



   // Philippine Date & Time
function updatePHTime() {
  const now = new Date();

  const dateOptions = {
    timeZone: "Asia/Manila",
    year: "numeric",
    month: "short",
    day: "numeric"
  };
  const dateFormatter = new Intl.DateTimeFormat("en-US", dateOptions);

  const timeOptions = {
    timeZone: "Asia/Manila",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: true
  };
  const timeFormatter = new Intl.DateTimeFormat("en-US", timeOptions);

  // Update all elements with class 'ph-time' using jQuery
  $(".ph-time").text(dateFormatter.format(now) + " | " + timeFormatter.format(now));
}

// Run once on load, then every second
setInterval(updatePHTime, 1000);
updatePHTime();

