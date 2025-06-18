<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Pengelolaan Data Statistik</title>
    <link rel="stylesheet" href="style.css">
        <style>
        /* CSS untuk styling pesan error */
        .error-message {
            color:rgb(255, 0, 0); /* Merah gelap */
            padding: 0px;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        /* CSS for table row hover effect */
        table tbody tr:hover {
            background-color: #ffffcc !important; /* Light yellow on hover */
            transition: background-color 0.3s ease; /* Smooth transition */
        }

        /* General table styling (improved from inline styles) */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden; /* Ensures rounded corners are visible */
        }

        table thead tr {
            background-color: #007bff;
            color: white;
        }

        table th, table td {
            padding: 5px 5px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        table td.actions {
            text-align: center;
            white-space: nowrap; /* Prevent actions from wrapping */
        }

        table td.actions a {
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 2px;
            font-weight: bold;
        }

        table td.actions .edit {
            background-color: #28a745; /* Green */
            color: white;
        }

        table td.actions .delete {
            background-color: #dc3545; /* Red */
            color: white;
        }

        /* Form and button styling */
        .button-group {
            margin-bottom: 15px;
            display: flex;
            gap: 10px; /* Space between buttons */
        }

        .button-group button {
            background-color: #6c757d; /* Grey button */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .button-group button:hover {
            background-color: #5a6268;
        }

        .button-group button a {
            color: white;
            text-decoration: none;
        }

        /* Message and error alert styling */
        .message-box {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
        }

        nav ul li {
            margin: 5px 0;
        }

        nav ul li a {
            text-decoration: none;
            padding: 10px;
            background-color: #0073e6;
            color: white;
            display: block;
            border-radius: 5px;
        }

        nav ul li a:hover {
            background-color: #005bb5;
        }
        </style>
</head>
<body>
    <div class="container">
        <h1>Manajemen Data Statistik Riau</h1>