<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>We'll Be Back Soon!</title>

  <!-- Particles.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body, html {
      height: 100%;
      background: #1e272e;
      font-family: 'Segoe UI', sans-serif;
      color: #fff;
      overflow: hidden;
    }

    #particles-js {
      position: absolute;
      width: 100%;
      height: 100%;
      z-index: 0;
    }

    .container {
      position: relative;
      z-index: 1;
      text-align: center;
      padding: 2rem;
      top: 15%;
    }

    .robot {
      width: 150px;
      animation: float 3s ease-in-out infinite;
    }

    h1 {
      font-size: 2.5rem;
      margin: 1rem 0;
      animation: bounce 2s infinite;
    }

    p {
      font-size: 1.2rem;
      margin-bottom: 1.5rem;
    }

    button {
      background-color: #da2442;
      border: none;
      color: white;
      padding: 10px 20px;
      font-size: 1rem;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s;
    }

    button:hover {
      background-color: #da24426b;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50%      { transform: translateY(-10px); }
    }

    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50%      { transform: translateY(-6px); }
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 2;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.6);
    }

    .modal-content {
      background-color: #2f3640;
      margin: 15% auto;
      padding: 20px;
      border-radius: 10px;
      width: 80%;
      max-width: 400px;
      color: #fff;
      position: relative;
    }

    .close {
      position: absolute;
      top: 10px; right: 15px;
      font-size: 24px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div id="particles-js"></div>

  <div class="container">
    <img src="https://gighz.net/wp-content/uploads/2023/09/Logo.svg" alt="Robot" class="robot" />
    <h1>Oops! We’re doing some work</h1>
    <p>Our Developers are fixing a few things.<br>We’ll be back shortly!</p>
    <button id="info-btn">Why am I seeing this?</button>
  </div>

  <!-- Modal -->
  <div class="modal" id="info-modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Maintenance in Progress</h2>
      <p>We're upgrading our systems for better performance and security. Thank you for your patience!</p>
    </div>
  </div>

  <script>
    // Load Particles
    particlesJS("particles-js", {
      particles: {
        number: { value: 60 },
        color: { value: "#ffffff" },
        shape: { type: "circle" },
        opacity: { value: 0.4 },
        size: { value: 3 },
        line_linked: {
          enable: true,
          distance: 150,
          color: "#ffffff",
          opacity: 0.4,
          width: 1
        },
        move: {
          enable: true,
          speed: 2,
          out_mode: "out"
        }
      },
      interactivity: {
        detect_on: "canvas",
        events: {
          onhover: { enable: true, mode: "repulse" }
        }
      },
      retina_detect: true
    });

    // Modal logic
    const btn = document.getElementById("info-btn");
    const modal = document.getElementById("info-modal");
    const close = document.querySelector(".close");

    btn.onclick = () => modal.style.display = "block";
    close.onclick = () => modal.style.display = "none";
    window.onclick = (e) => {
      if (e.target == modal) modal.style.display = "none";
    };
  </script>
</body>
</html>
