<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SkillSwap | Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@300;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #FFDEE9 0%, #B5FFFC 100%);
            font-family: 'Poppins', sans-serif;
            color: #333;
            overflow-x: hidden;
            position: relative;
        }

        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
            padding: 0 20px;
            position: relative;
            animation: fadeIn 1.2s ease-out;
        }

        h1 {
            font-family: 'Great Vibes', cursive;
            font-size: 64px;
            color: #3a3a3a;
            z-index: 2;
        }

        p {
            font-size: 24px;
            margin-top: 10px;
            margin-bottom: 40px;
            color: #555;
            position: relative;
            z-index: 2;
        }

        .btn-group a {
            margin: 10px;
            padding: 14px 32px;
            background-color: #B8E2DC;
            border: none;
            border-radius: 14px;
            color: #333;
            text-decoration: none;
            font-size: 18px;
            font-weight: 500;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-group a:hover {
            background-color: #A0DAD4;
            transform: scale(1.05);
        }

        .doodle {
            position: absolute;
            width: 80px;
            opacity: 0.6;
            animation: float 6s ease-in-out infinite;
        }

        .doodle1 { top: 8%; left: 5%; animation-delay: 0s; }
        .doodle2 { top: 20%; right: 8%; animation-delay: 1s; }
        .doodle3 { bottom: 12%; left: 10%; animation-delay: 2s; }
        .doodle4 { bottom: 10%; right: 15%; animation-delay: 3s; }
        .doodle5 { top: 28%; left: 48%; transform: translate(-50%, -50%); animation-delay: 4s; }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Optional background swirl/paint splash */
        .background-splash {
            position: absolute;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            opacity: 0.1;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="hero">
        <!-- Floating SVG doodles -->
        <img src="images/alarm_watch.svg" class="doodle doodle1" alt="alarm">
        <img src="images/exchangearrow.svg" class="doodle doodle2" alt="exchange">
        <img src="images/magnifying-glas.svg" class="doodle doodle3" alt="magnify">
        <img src="images/record.svg" class="doodle doodle4" alt="record">
        <img src="images/reminder-alert.svg" class="doodle doodle5" alt="alert">

        <!-- Optional background splash (if you add a paint-style SVG later) -->
        <!-- <img src="images/splash.svg" class="background-splash" alt="background design"> -->

        <h1>Welcome to SkillSwap</h1>
        <p>Grow Together, Learn Forever.</p>

        <div class="btn-group">
            <a href="signup.php">Sign Up</a>
            <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>
