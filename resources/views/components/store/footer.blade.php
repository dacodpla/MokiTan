<footer class="mt-20 bg-brand text-canvas">
    <div class="container mx-auto py-12 grid grid-cols-1 md:grid-cols-3 items-center gap-8">
        {{-- Social Media (left) --}}
        <div>
            <h4 class="type-h4 mb-3">Social Media</h4>
            <ul class="space-y-1 type-body">
                <li><a href="#" class="hover:underline">Instagram</a></li>
                <li><a href="#" class="hover:underline">X/Twitter</a></li>
                <li><a href="#" class="hover:underline">Our WhatsApp Channel</a></li>
            </ul>
        </div>

        {{-- Light logo (center) --}}
        <div class="flex justify-center">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="{{ asset('img/logo/MokiTanLT.svg') }}" alt="Moki-Tan" class="h-16 w-auto">
            </a>
        </div>

        {{-- Legal (right) --}}
        <div class="md:text-right">
            <h4 class="type-h4 mb-3">Legal</h4>
            <ul class="space-y-1 type-body">
                <li><a href="#" class="hover:underline">Terms and Conditions</a></li>
                <li><a href="#" class="hover:underline">Refund Policy</a></li>
                <li><a href="#" class="hover:underline">Customer Service</a></li>
            </ul>
        </div>
    </div>
</footer>
