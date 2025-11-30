@extends('layouts.dashboard')

@section('title', 'Verification Pending - SKY LAINI')

@push('styles')
<style>
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .form-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }
    .form-input:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: #f59e0b;
        outline: none;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
    }
</style>
@endpush

@section('content')
<div class="min-h-[80vh] flex items-center justify-center">
    <div class="max-w-2xl w-full">
        <div class="glass-card rounded-3xl p-8 md:p-12 text-center relative overflow-hidden">
            <!-- Background Glow -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-amber-500/5 blur-3xl -z-10"></div>

            <div class="w-24 h-24 mx-auto bg-amber-500/20 rounded-full flex items-center justify-center mb-6 animate-pulse">
                <svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-white mb-4">Akaunti Yako Inasubiri Uhakiki</h1>
            <p class="text-gray-400 text-lg mb-8">
                Asante kwa kujiunga nasi! Ili kuanza kupokea kazi, tunahitaji uhakiki taarifa zako. Tafadhali pakia nyaraka zifuatazo ili admin aweze kukuhakiki.
            </p>

            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-xl mb-6 text-left">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-500/10 border border-green-500/20 text-green-500 p-4 rounded-xl mb-6 flex items-center gap-2 text-left">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($agent->documents->count() > 0)
                <!-- Waiting State -->
                <div class="bg-amber-500/10 border border-amber-500/20 p-8 rounded-3xl mb-8">
                    <div class="w-16 h-16 mx-auto bg-amber-500/20 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">Nyaraka Zimepokelewa</h2>
                    <p class="text-gray-400 mb-6">
                        Tumepokea nyaraka zako na zinafanyiwa uhakiki. Tafadhali subiri, utapokea ujumbe punde uhakiki utakapokamilika.
                    </p>
                    <div class="flex flex-col gap-2">
                         @foreach($agent->documents as $doc)
                            <div class="flex items-center gap-3 bg-white/5 p-3 rounded-xl">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-gray-300 text-sm">{{ $doc->file_name }}</span>
                                <span class="text-xs text-amber-500 bg-amber-500/10 px-2 py-1 rounded ml-auto uppercase font-bold">Inahakikiwa</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Upload Form -->
                <form action="{{ route('agent.documents.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-left">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-white font-bold mb-2">Namba ya NIDA</label>
                            <input type="text" name="nida_number" value="{{ old('nida_number', $agent->nida_number) }}" class="form-input w-full rounded-xl px-4 py-3" placeholder="Ingiza namba yako ya NIDA" required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-white font-bold mb-2">Kitambulisho (NIDA/Kura)</label>
                                <div class="relative group">
                                    <input type="file" name="id_document" id="id_document" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required onchange="updateFileName(this, 'id_preview')">
                                    <div class="bg-white/5 border border-dashed border-white/20 rounded-xl p-4 text-center group-hover:bg-white/10 transition flex flex-col items-center justify-center min-h-[100px]">
                                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        <span id="id_preview" class="text-gray-400 text-sm px-2 truncate w-full">Bonyeza kupakia picha</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-white font-bold mb-2">Picha ya Pasipoti</label>
                                <div class="relative group">
                                    <input type="file" name="passport_photo" id="passport_photo" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required onchange="updateFileName(this, 'passport_preview')">
                                    <div class="bg-white/5 border border-dashed border-white/20 rounded-xl p-4 text-center group-hover:bg-white/10 transition flex flex-col items-center justify-center min-h-[100px]">
                                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        <span id="passport_preview" class="text-gray-400 text-sm px-2 truncate w-full">Bonyeza kupakia picha</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-black text-lg rounded-xl shadow-lg shadow-amber-500/20 transition transform hover:scale-[1.02]">
                        Tuma kwa Uhakiki
                    </button>
                </form>
            @endif

            <div class="mt-8 pt-6 border-t border-white/10">
                <p class="text-gray-500 text-sm">
                    Uhakiki unaweza kuchukua saa 24-48. Utapokea ujumbe ukikamilika.
                </p>
                <form action="{{ route('logout') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" class="text-red-500 hover:text-red-400 font-bold text-sm">Ondoka (Logout)</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateFileName(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            preview.textContent = input.files[0].name;
            preview.classList.add('text-green-500', 'font-bold');
            preview.classList.remove('text-gray-400');
        } else {
            preview.textContent = 'Bonyeza kupakia picha';
            preview.classList.remove('text-green-500', 'font-bold');
            preview.classList.add('text-gray-400');
        }
    }
</script>
@endpush
