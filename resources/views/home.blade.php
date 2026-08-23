<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Adaptia</title>
</head>

<body class="font-sans">
    <header class="flex items-center content-center p-4 px-8">

        <div class="flex-1">

            <img class="w-50 h-auto" src="{{ asset('images/logotipo.png') }}" alt="logotipo de adaptia">

        </div>
        <div class="flex-1">

            <ul class="flex justify-between gap-4 text-[#3e5a51] font-bold">
                <li><a href="#funcionamiento">Cómo funciona</a></li>
                <li><a href="#catalogo">Catálogo</a></li>
                <li><a href="#nosotros">Nosotros</a></li>
            </ul>

        </div>
        <div class="flex-1 flex justify-end">

            <a class="bg-[#7cb22b] text-white px-7 py-3 rounded-[20px] text-[19px] font-bold hover:scale-110 transition duration-300"
                href="#">Empezar</a>

        </div>

    </header>

    <main>

        <section class="flex reveal">

            <div class="flex-1 flex p-8 py-20 px-20 pl-30 flex-col gap-5">

                <h1 class="flex gap-0 text-[#0f3c2b]  text-[4em] font-bold">Encuentra la planta perfecta para tu <br>
                    espacio</h1>
                <p class="flex text-[1.5em] text-[#787878]">Nuestro sistema analiza las condiciones reales <br> de tu
                    hogar y te recomienda las plantas que <br> mejor se adaptan a ti.</p>
                <a class="flex items-center px-1 py-5 gap-6 justify-center text-[1.5em] w-[65%] bg-[#7cb22b] text-white rounded-[20px] font-bold hover:scale-110 transition duration-300"
                    href="#">Empezar ahora <img class="w-[1em] h-auto" src="{{ asset('images/hora_blanca.png') }}" alt=""></a>
                <p class="flex items-center gap-4 text-[1.2em] text-[#787878]"> <img class="w-[1.4em] h-auto" src="{{ asset('images/check_verde.png') }}" alt=""> Recomendaciones 100% personalizadas</p>
            </div>
            <div class="flex-1 flex items-center justify-center p-4 pr-10">

                <img class="w-170 h-auto" src="{{ asset('images/hero-image.png') }}" alt="imagen de una planta">

            </div>

        </section>

        <section class="flex flex-col items-center justify-center gap-2 p-20 bg-[#f6f7f6] reveal" id="funcionamiento">

            <div class="flex items-center justify-center text-[#0d3225] font-bold text-[2.5em]">
                <h2>Cómo funciona</h2>
            </div>
            <div class="flex items-center p-6">

                <div class="flex flex-col items-center justify-center gap-4">

                    <div class="flex items-center justify-center w-40 h-40 bg-[#eef0e9] rounded-full">

                        <img class="w-30 h-30 object-contain" src="{{ asset('images/paso_1.png') }}"
                            alt="imagen de un tablero verde">

                    </div>

                    <h3 class="flex items-center justify-center w-10 h-10 bg-[#80b42e] text-white rounded-full">1</h3>
                    <h3 class="text-[#304a43] text-[1.5em] font-bold text-center">Cuéntanos sobre <br> tu espacio</h3>
                    <p class="text-center text-[#696c6d]">Responde algunas preguntas sobre la luz, riesgo, espacio,
                        experiencia y si tienes mascotas</p>

                </div>

                <div class="flex items-start h-50">

                    <img class="w-50 h-auto" src="{{ asset('images/separacion.png') }}"
                        alt="separacion de puntos verdes">

                </div>

                <div class="flex flex-col items-center justify-center gap-4">

                    <div class="flex items-center justify-center w-40 h-40 bg-[#eef0e9] rounded-full">

                        <img class="w-30 h-30 object-contain" src="{{ asset('images/paso_2.png') }}"
                            alt="imagen de un tablero verde">

                    </div>

                    <h3 class="flex items-center justify-center w-10 h-10 bg-[#80b42e] text-white rounded-full">2</h3>
                    <h3 class="text-[#304a43] text-[1.5em] font-bold text-center">Recibe recomendaciones <br>
                        personalizadas</h3>
                    <p class="text-center text-[#696c6d]">Nuestro sistema analiza la información y encuentra las plantas
                        ideales para ti y tu entorno</p>

                </div>

                <div class="flex items-start h-50">

                    <img class="w-50 h-auto" src="{{ asset('images/separacion.png') }}"
                        alt="separacion de puntos verdes">

                </div>

                <div class="flex flex-col items-center justify-center gap-4">

                    <div class="flex items-center justify-center w-40 h-40 bg-[#eef0e9] rounded-full">

                        <img class="w-30 h-30 object-contain" src="{{ asset('images/paso_3.png') }}"
                            alt="imagen de un tablero verde">

                    </div>

                    <h3 class="flex items-center justify-center w-10 h-10 bg-[#80b42e] text-white rounded-full">3</h3>
                    <h3 class="text-[#304a43] text-[1.5em] font-bold text-center">Elige tu planta <br> ideal</h3>
                    <p class="text-center text-[#696c6d]">Explora las opciones recomendadas y elige la planta perfecta
                        para convertir tu espacio</p>

                </div>

            </div>

        </section>

        <section class="flex flex-col items-center px-3 py-20 gap-8 reveal" id="catalogo">

            <div class="flex flex-col items-center justify-center w-full">
                <h2 class="text-[2.5em] text-[#063829] font-bold">Plantas recomendadas para ti</h2>
                <p class="text-[1.4em] text-[#818181]">Seleccionadas según las condiciones de tu espacio</p>
            </div>

            <div class="grid grid-cols-4 px-10 gap-15 w-full">
                <div
                    class="overflow-hidden flex flex-col items-center justify-center rounded-[2em] border-3 border-[#ecedea]">

                    <div class="flex items-center justify-center bg-[#f1f1ef] h-62.5 w-full">

                        <img class="w-[80%] h-full object-contain" src="{{ asset('images/sansevieria.png') }}"
                            alt="imagen de planta sansevieria">

                    </div>
                    <div class="flex flex-col w-full pl-6 pt-5 gap-3 border-t-3 border-[#ecedea]">

                        <h3 class="text-[1.4em] tex-[#0b271e] font-semibold">Sansevieria</h3>
                        <p class="text-[#747474]">Resistente y purificadora</p>

                        <div class="flex py-4 gap-6 text-[#747474] font-semibold">

                            <p class="flex items-center gap-1"><img class="w-[2em] h-auto" src="{{ asset('images/sol.png') }}" alt="">Luz baja</p>
                            <p class="flex items-center gap-1"><img class="w-[2em] h-auto" src="{{ asset('images/gota.png') }}" alt="">Riesgo bajo</p>

                        </div>

                    </div>

                </div>

                <div
                    class="overflow-hidden flex flex-col items-center justify-center rounded-[2em] border-3 border-[#ecedea]">

                    <div class="flex items-center justify-center bg-[#f1f1ef] h-62.5 w-full">

                        <img class="w-[80%] h-full object-contain" src="{{ asset('images/photo.png') }}"
                            alt="imagen de planta photo">

                    </div>
                    <div class="flex flex-col w-full pl-6 pt-5 gap-3 border-t-3 border-[#ecedea]">

                        <h3 class="text-[1.4em] tex-[#0b271e] font-semibold">Photo</h3>
                        <p class="text-[#747474]">Fácil de cuidar</p>

                        <div class="flex py-4 gap-6 text-[#747474] font-semibold">

                            <p class="flex items-center gap-1"><img class="w-[2em] h-auto" src="{{ asset('images/sol.png') }}" alt="">Luz media</p>
                            <p class="flex items-center gap-1"><img class="w-[2em] h-auto" src="{{ asset('images/gota.png') }}" alt="">Riesgo moderado</p>

                        </div>

                    </div>

                </div>

                <div
                    class="overflow-hidden flex flex-col items-center justify-center rounded-[2em] border-3 border-[#ecedea]">

                    <div class="flex items-center justify-center bg-[#f1f1ef] h-62.5 w-full">

                        <img class="w-[80%] h-full object-contain" src="{{ asset('images/lirio_paz.png') }}"
                            alt="imagen de planta lirio de paz">

                    </div>
                    <div class="flex flex-col w-full pl-6 pt-5 gap-3 border-t-3 border-[#ecedea]">

                        <h3 class="text-[1.4em] tex-[#0b271e] font-semibold">Lirio de paz</h3>
                        <p class="text-[#747474]">Flor elegante y purificadora</p>

                        <div class="flex py-4 gap-6 text-[#747474] font-semibold">

                            <p class="flex items-center gap-1"><img class="w-[2em] h-auto" src="{{ asset('images/sol.png') }}" alt="">Luz baja</p>
                            <p class="flex items-center gap-1"><img class="w-[2em] h-auto" src="{{ asset('images/gota.png') }}" alt="">Riesgo moderado</p>

                        </div>

                    </div>

                </div>

                <div
                    class="overflow-hidden flex flex-col items-center justify-center rounded-[2em] border-3 border-[#ecedea]">

                    <div class="flex items-center justify-center bg-[#f1f1ef] h-62.5 w-full">

                        <img class="w-[80%] h-full object-contain" src="{{ asset('images/zamioculca.png') }}"
                            alt="imagen de planta zamioculca">

                    </div>
                    <div class="flex flex-col w-full pl-6 pt-5 gap-3 border-t-3 border-[#ecedea]">

                        <h3 class="text-[1.4em] tex-[#0b271e] font-semibold">Zamioculca</h3>
                        <p class="text-[#747474]">Ideal para principiantes</p>

                        <div class="flex py-4 gap-6 text-[#747474] font-semibold">

                            <p class="flex items-center gap-1"><img class="w-[2em] h-auto" src="{{ asset('images/sol.png') }}" alt="">Luz baja</p>
                            <p class="flex items-center gap-1"><img class="w-[2em] h-auto" src="{{ asset('images/gota.png') }}" alt="">Riesgo bajo</p>

                        </div>

                    </div>

                </div>


            </div>

            <div>

                <a class="flex items-center justify-center gap-6 py-5 px-20 border-3 border-[#cee5b8] rounded-[1.2em] font-semibold text-[#92b866] text-[1.3em] hover:scale-110 transition duration-300"
                    href="#">Ver catálogo completo <img class="w-[1em] h-auto" src="{{ asset('images/hoja_verde.png') }}" alt=""></a>

            </div>

        </section>

        <section class="flex items-center justify-center py-20 px-40 bg-[#f7f8f6] reveal" id="nosotros">

            <div class="flex-1 flex flex-col items-start justify-center gap-10">

                <h2 class="text-[2.5em] text-[#063829] font-bold">¿Por qué elegir Adaptia?</h2>

                <div class="flex items-center gap-6">
                    <div class="flex items-center justify-center p-3 bg-[#7aae2a] rounded-[50%]">

                        <img class="size-[2em]" src="{{ asset('images/hoja.png') }}" alt="imagen de una plantita">

                    </div>
                    <div>
                        <p class="text-[#315b4a] font-semibold text-[1.2em]">Recomendaciones precisas</p>
                        <p class="text-[#737f7d]">Basadas en datos reales de tu entorno</p>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex items-center justify-center p-3 bg-[#7aae2a] rounded-[50%]">

                        <img class="size-[2em]" src="{{ asset('images/reloj.png') }}" alt="imagen de una reloj">

                    </div>
                    <div>
                        <p class="text-[#315b4a] font-semibold text-[1.2em]">Recomendaciones precisas</p>
                        <p class="text-[#737f7d]">Basadas en datos reales de tu entorno</p>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex items-center justify-center p-3 bg-[#7aae2a] rounded-[50%]">

                        <img class="size-[2em]" src="{{ asset('images/corazon.png') }}" alt="imagen de un corazon">

                    </div>
                    <div>
                        <p class="text-[#315b4a] font-semibold text-[1.2em]">Recomendaciones precisas</p>
                        <p class="text-[#737f7d]">Basadas en datos reales de tu entorno</p>
                    </div>
                </div>



            </div>
            <div class="flex-1 flex items-center justify-center">

                <img class="w-[60%] h-auto" src="{{ asset('images/logo.png') }}" alt="imagen del logo">

            </div>

        </section>

    </main>
    <footer class="flex flex-col items-center px-50 justify-center text-white bg-[#053a28] gap-10 w-full reveal">

        <div class="flex gap-20 w-full pt-10">

            <div class="flex-1 flex flex-col items-start justify-center text-left gap-6 text-[1.1em]">

                <img class="w-[60%] h-auto" src="{{ asset('images/logotipo_blanco.png') }}" alt="logotipo de adaptia con color blanco">

                <p>Tecnología y naturaleza para <br> ayudarte a vivir en armonía <br> con las plantas</p>

            </div>
            <div class="flex-2 flex justify-between text-[1.1em]">

                <div class="flex flex-col pt-11 gap-6">

                    <h3 class="font-bold">Navegación</h3>
                    <ul class="flex flex-col gap-3">
                        <li><a href="#funcionamiento">Cómo funciona</a></li>
                        <li><a href="#catalogo">Catálogo</a></li>
                        <li><a href="#nosotros">Nosotros</a></li>
                    </ul>

                </div>
                <div class="flex flex-col pt-11 gap-6">

                    <h3 class="font-bold">Recursos</h3>
                    <ul class="flex flex-col gap-3">
                        <li>Guía de cuidados</li>
                        <li>Preguntas frecuentes</li>
                        <li>Blog</li>
                    </ul>

                </div>
                <div class="flex flex-col pt-11 gap-6">

                    <h3 class="font-bold">Legal</h3>
                    <ul class="flex flex-col gap-3">
                        <li>Términos y condiciones</li>
                        <li>Política de privacidad</li>
                        <li>Cookies</li>
                    </ul>

                </div>

            </div>

        </div>



        <p>© 2026 Adaptia. Todos los derechos reservados</p>

    </footer>
</body>

</html>
