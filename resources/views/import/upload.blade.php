<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Импорт Excel файла</title>
</head>
<body>
<h1>Импорт Excel файла</h1>
<form action="{{ route('import.process') }}" method="post" enctype="multipart/form-data">
    @csrf
    <label for="file">Выберите Excel файл (.xlsx):</label>
    <input type="file" name="file" id="file" accept=".xlsx">
    <button type="submit">Импортировать</button>
</form>
</body>
</html>