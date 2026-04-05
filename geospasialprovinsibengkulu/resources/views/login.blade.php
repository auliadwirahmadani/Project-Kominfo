<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="min-h-screen bg-cover bg-center relative"
      style="background-image: url('bg 2.png');">

<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-5xl bg-white shadow-xl rounded-2xl flex overflow-hidden">

        <!-- KIRI (FORM LOGIN) -->
        <div class="w-full lg:w-1/2 p-6 sm:p-10">

            <div class="text-center">
                <img src="Logo Provinsi Bengkulu.png" class="w-20 mx-auto" />
            </div>

            <div class="mt-6 text-center">
                <h1 class="text-2xl font-extrabold text-red-700">Login</h1>
                <p class="text-gray-500 mt-1 text-sm">Masuk ke Akun Geoportal</p>
            </div>

            <!-- ERROR MESSAGE -->
            @if(session('error'))
            <div class="mt-4 text-red-600 text-sm text-center">
                {{ session('error') }}
            </div>
            @endif

            <!-- FORM -->
            <form method="POST" action="{{ route('login.process') }}" class="mt-6 space-y-4">
                @csrf

                <input
                    name="email"
                    type="email"
                    placeholder="Email"
                    required
                    class="w-full px-4 py-2.5 rounded-lg bg-gray-100 border border-gray-200 text-sm focus:outline-none focus:border-red-400 focus:bg-white"
                />

                <input
                    name="password"
                    type="password"
                    placeholder="Password"
                    required
                    class="w-full px-4 py-2.5 rounded-lg bg-gray-100 border border-gray-200 text-sm focus:outline-none focus:border-red-400 focus:bg-white"
                />

                <button
                    type="submit"
                    class="w-full py-2.5 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition">
                    Masuk
                </button>
            </form>

            <p class="text-xs text-gray-500 text-center mt-4">
                Masuk Berarti Menyetujui
                <span class="text-red-600 font-medium">Syarat & Ketentuan</span>
            </p>
        </div>

        <!-- KANAN (ILUSTRASI) -->
        <div class="hidden lg:flex lg:w-1/2 bg-red-600 items-center justify-center">
            <div class="p-8 text-center text-white">
                <h2 id="typingTitle" class="text-3xl font-bold mb-3"></h2>
                <p id="typingText" class="opacity-90 text-base">
                    Akses data geospasial provinsi Bengkulu secara cepat dan terintegrasi.
                </p>

                <img
                    class="mt-8 w-64 lg:w-80 xl:w-96 mx-auto animate-float drop-shadow-2xl"
                    src="UI uwik.png">
            </div>
        </div>

    </div>
</div>

<script>
const titleText = "Geoportal Provinsi Bengkulu";
const speed = 60;
let j = 0;

function typeTitle() {
  if (j < titleText.length) {
    document.getElementById("typingTitle").innerHTML += titleText.charAt(j);
    j++;
    setTimeout(typeTitle, speed);
  }
}

typeTitle();
</script>


</body>
</html>
