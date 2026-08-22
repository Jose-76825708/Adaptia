<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite('resources/css/app.css')
    <title>Adaptia</title>
</head>

<body class="font-sans">
    <header class="flex items-center content-center p-4 px-8">

        <div class="flex-1">

            <img class="w-50 h-auto" src="{{ asset('images/logotipo.png') }}" alt="logotipo de adaptia">

        </div>
        <div class="flex-1">

            <ul class="flex justify-between gap-4 text-[#3e5a51] font-bold">
                <li><a href="#">Cómo funciona</a></li>
                <li><a href="#">Catálogo</a></li>
                <li><a href="#">Nosotros</a></li>
            </ul>

        </div>
        <div class="flex-1 flex justify-end">

            <a class="bg-[#7cb22b] text-white px-7 py-3 rounded-[20px] text-[19px] font-bold hover:scale-110 transition duration-300"
                href="#">Empezar</a>

        </div>

    </header>

    <main>

        <section class="flex">

            <div class="flex-1 flex p-8 py-20 px-20 pl-30 flex-col gap-5">

                <h1 class="flex gap-0 text-[#0f3c2b]  text-[4em] font-bold">Encuentra la planta perfecta para tu <br> espacio</h1>
                <p class="flex text-[1.5em] text-[#787878]">Nuestro sistema analiza las condiciones reales <br> de tu hogar y te recomienda las plantas que <br> mejor se adaptan a ti.</p>
                <a class="flex items-center px-1 py-5 justify-center text-[1.5em] w-[65%] bg-[#7cb22b] text-white rounded-[20px] font-bold hover:scale-110 transition duration-300" href="#">Empezar ahora</a>
                <p class="flex text-[1.2em] text-[#787878]">Recomendaciones 100% personalizadas</p>
            </div>
            <div class="flex-1 flex items-center justify-center p-4 pr-10">

                <img class="w-170 h-auto" src="{{ asset('images/hero-image.png') }}" alt="imagen de una planta">

            </div>

        </section>

    </main>
    <footer>

    </footer>
</body>

</html>
