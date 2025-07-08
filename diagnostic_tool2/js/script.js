function copyText(text) {
  navigator.clipboard.writeText(text).then(() => {
    new bootstrap.Toast(document.getElementById("copyToast")).show();
  });
}

// Search Filter
document.getElementById("search").addEventListener("input", function () {
  const query = this.value.toLowerCase();
  document.querySelectorAll(".command-item").forEach(item => {
    item.style.display = item.textContent.toLowerCase().includes(query) ? "block" : "none";
  });
});

// Category Filter
document.querySelectorAll(".category-link").forEach(link => {
  link.addEventListener("click", function (e) {
    e.preventDefault();
    const selected = this.dataset.category;
    document.querySelectorAll(".command-item").forEach(item => {
      const cat = item.dataset.category;
      item.style.display = (selected === "all" || selected === cat) ? "block" : "none";
    });
    document.querySelectorAll(".category-link").forEach(l => l.classList.remove("active"));
    this.classList.add("active");
  });
});

// Dark Mode Toggle
document.getElementById("darkModeSwitch").addEventListener("change", function () {
  document.body.classList.toggle("dark-mode", this.checked);
  localStorage.setItem("darkMode", this.checked ? "on" : "off");
});
window.addEventListener("load", function () {
  const dark = localStorage.getItem("darkMode") === "on";
  document.getElementById("darkModeSwitch").checked = dark;
  document.body.classList.toggle("dark-mode", dark);
});

// Syntax Checker
document.getElementById("syntaxInput").addEventListener("keyup", function (e) {
  const value = this.value.trim();
  const result = document.getElementById("syntaxResult");
  const toast = new bootstrap.Toast(document.getElementById("syntaxToast"));

  const valid = /^(ipconfig|ping|tracert|netstat|nslookup|netsh|arp|systeminfo|sfc|chkdsk|shutdown|wmic|hostname|getmac)(\s+\/[\w-]+)?$/i;

  if (value === "") {
    result.innerText = "";
  } else if (valid.test(value)) {
    result.innerHTML = "✅ Looks like a valid command.";
    result.classList.add("text-success");
    result.classList.remove("text-danger");
  } else {
    result.innerHTML = "❌ Might be an invalid or incomplete command.";
    result.classList.add("text-danger");
    result.classList.remove("text-success");
    if (e.key === "Enter") toast.show();
  }
});

// Simulate Command Output
function simulateCommand() {
  const input = document.getElementById("runInput").value.trim().toLowerCase();
  const output = document.getElementById("runOutput");
  const resultBox = document.getElementById("runResult");

  const fakeOutputs = {
    ipconfig: "IPv4 Address: 192.168.1.101\\nSubnet Mask: 255.255.255.0\\nDefault Gateway: 192.168.1.1",
    ping: "Pinging 8.8.8.8 with 32 bytes of data:\\nReply from 8.8.8.8: bytes=32 time=14ms TTL=55",
    hostname: "MY-COMPUTER",
    systeminfo: "Host Name: MY-PC\\nOS Version: Windows 11 Pro\\nBuild: 22621",
  };

  output.innerText = fakeOutputs[input] || "❌ Command not supported for simulation.";
  resultBox.classList.remove("d-none");
}
