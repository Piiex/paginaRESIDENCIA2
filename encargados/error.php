<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 450px;
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            color: #4a4a4a;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }
        p {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn {
            background-color: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s;
            font-weight: 600;
            display: inline-block;
        }
        .btn:hover {
            background-color: #764ba2;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>Acceso Denegado</h1>
        <p>Lo sentimos, no tienes permiso para acceder a esta página. Si crees que esto es un error, por favor contacta al administrador del sitio.</p>
        <a href="../dashboard.php" class="btn">Volver a la página principal</a>
    </div>
</body>
</html>