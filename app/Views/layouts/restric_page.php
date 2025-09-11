<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Access Denied</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
    }

    body {
      height: 100vh;
      background: linear-gradient(135deg, #f11176ff, #da2442);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .card {
      background: #fff;
      padding: 3rem;
      border-radius: 1rem;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      text-align: center;
      animation: fadeIn 0.8s ease-in-out;
    }

    .card h1 {
      font-size: 3rem;
      color: #dc3545;
      margin-bottom: 1rem;
    }

    .card p {
      font-size: 1.2rem;
      color: #333;
      margin-bottom: 2rem;
    }

    .card a {
      display: inline-block;
      padding: 0.75rem 1.5rem;
      background-color: #da2442;
      color: white;
      text-decoration: none;
      border-radius: 0.5rem;
      transition: background 0.3s;
    }

    .card a:hover {
      background-color: #b30000ff;
    }

    .emoji {
      font-size: 5rem;
      animation: bounce 1.5s infinite;
    }

    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="emoji">⛔️</div>
    <h1>Access Denied</h1>
    <p>You are not allowed to access this page.<br>Please contact your administrator if you believe this is a mistake.</p>
    <a href="<?=base_url('/dashboard')?>">Return to Home</a>
  </div>
</body>
</html>
