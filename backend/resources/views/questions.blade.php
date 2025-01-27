<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Formulario</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="container mx-auto mt-10">
        <h1 class="text-3xl font-bold mb-6">Detalles del Formulario</h1>

        <h2 class="text-2xl font-semibold mb-4">Título: {{ $form->title }}</h2>

        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2">Pregunta</th>
                    <th class="border border-gray-300 px-4 py-2">Respuestas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($questions as $question)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{ $question['question'] }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            {{ implode(', ', $question['answers']) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
