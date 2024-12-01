<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .back {
            text-decoration: none; 
            padding: 10px 15px; 
            margin-left: 30px;
            background-color: #A8D5BA; 
            color: #000; 
            border-radius: 5px; 
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            font-family: 'Arial', sans-serif;
            font-weight: bold;
        }

        .back:hover {
            background-color: #8bc59d;
            color: #000; 
        }

        /* responsive to screen width */
        @media (max-width: 600px) {
            .back span {
                display: none;
            }
        }
    </style>
</head>
<body>

<a class="back" href="javascript:history.back()">
    &larr; <span>Go Back</span>
</a>

</body>
</html>
