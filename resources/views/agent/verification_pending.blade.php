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
    .step-indicator {
        transition: all 0.3s ease;
    }
    .step-indicator.active {
        background: linear-gradient(135deg, #f59e0b, #ea580c);
        color: black;
    }
    .step-indicator.completed {
        background: #22c55e;
        color: white;
    }
    .face-upload-box {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .face-upload-box:hover {
        transform: scale(1.02);
        border-color: #f59e0b;
    }
    .face-upload-box.has-image {
        border-color: #22c55e;
    }
    .face-upload-box.has-image .upload-icon {
        display: none;
    }
    .face-upload-box .preview-img {
        display: none;
    }
    .face-upload-box.has-image .preview-img {
        display: block;
    }
</style>
@endpush

@section('content')
@php
    $hasDocuments = $agent->documents->count() > 0;
    $faceVerification = $agent->faceVerification;
    $hasFaceVerification = $faceVerification && $faceVerification->isComplete();
    
    // Determine current step
    $currentStep = 1; // Documents
    if ($hasDocuments) {
        $currentStep = 2; // Face Verification
    }
    if ($hasFaceVerification) {
        $currentStep = 3; // Waiting for review
    }
@endphp

<div class="min-h-[80vh] flex items-center justify-center py-8">
    <div class="max-w-3xl w-full">
        <div class="glass-card rounded-3xl p-8 md:p-12 relative overflow-hidden">
            <!-- Background Glow -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-amber-500/5 blur-3xl -z-10"></div>

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 mx-auto bg-amber-500/20 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-white mb-2">Akaunti Yako Inasubiri Uhakiki</h1>
                <p class="text-gray-400">Kamilisha hatua zote ili uweze kuanza kupokea kazi</p>
            </div>

            <!-- Step Indicators -->
            <div class="flex items-center justify-center gap-4 mb-10">
                <!-- Step 1: Documents -->
                <div class="flex items-center gap-3">
                    <div class="step-indicator w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg {{ $hasDocuments ? 'completed' : 'active' }}">
                        @if($hasDocuments)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @else
                            1
                        @endif
                    </div>
                    <span class="text-white font-bold hidden sm:block">Nyaraka</span>
                </div>

                <!-- Connector -->
                <div class="w-12 h-1 rounded {{ $hasDocuments ? 'bg-green-500' : 'bg-white/20' }}"></div>

                <!-- Step 2: Face Verification -->
                <div class="flex items-center gap-3">
                    <div class="step-indicator w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg {{ $hasFaceVerification ? 'completed' : ($hasDocuments ? 'active' : 'bg-white/10 text-gray-500') }}">
                        @if($hasFaceVerification)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @else
                            2
                        @endif
                    </div>
                    <span class="font-bold hidden sm:block {{ $hasDocuments ? 'text-white' : 'text-gray-500' }}">Uso</span>
                </div>

                <!-- Connector -->
                <div class="w-12 h-1 rounded {{ $hasFaceVerification ? 'bg-green-500' : 'bg-white/20' }}"></div>

                <!-- Step 3: Review -->
                <div class="flex items-center gap-3">
                    <div class="step-indicator w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg {{ $hasFaceVerification ? 'active' : 'bg-white/10 text-gray-500' }}">
                        3
                    </div>
                    <span class="font-bold hidden sm:block {{ $hasFaceVerification ? 'text-white' : 'text-gray-500' }}">Uhakiki</span>
                </div>
            </div>

            <!-- Errors -->
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

            <!-- STEP 1: Documents Upload -->
            @if(!$hasDocuments)
                <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Hatua ya 1: Pakia Nyaraka</h2>
                            <p class="text-gray-400 text-sm">Pakia kitambulisho chako na picha ya passport</p>
                        </div>
                    </div>

                    <form action="{{ route('agent.documents.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label class="block text-white font-bold mb-2">Namba ya NIDA</label>
                            <input type="text" name="nida_number" value="{{ old('nida_number', $agent->nida_number) }}" class="form-input w-full rounded-xl px-4 py-3" placeholder="Ingiza namba yako ya NIDA" required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-white font-bold mb-2">Kitambulisho (NIDA/Kura)</label>
                                <div class="relative group">
                                    <input type="file" name="id_document" id="id_document" accept="image/*,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required onchange="updateFileName(this, 'id_preview')">
                                    <div class="bg-white/5 border border-dashed border-white/20 rounded-xl p-4 text-center group-hover:bg-white/10 transition flex flex-col items-center justify-center min-h-[100px]">
                                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        <span id="id_preview" class="text-gray-400 text-sm px-2 truncate w-full">Bonyeza kupakia picha</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-white font-bold mb-2">Picha ya Pasipoti</label>
                                <div class="relative group">
                                    <input type="file" name="passport_photo" id="passport_photo" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required onchange="updateFileName(this, 'passport_preview')">
                                    <div class="bg-white/5 border border-dashed border-white/20 rounded-xl p-4 text-center group-hover:bg-white/10 transition flex flex-col items-center justify-center min-h-[100px]">
                                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        <span id="passport_preview" class="text-gray-400 text-sm px-2 truncate w-full">Bonyeza kupakia picha</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-black text-lg rounded-xl shadow-lg shadow-amber-500/20 transition transform hover:scale-[1.02]">
                            Endelea ➜ Hatua ya 2
                        </button>
                    </form>
                </div>

            <!-- STEP 2: Face Verification Upload -->
            @elseif(!$hasFaceVerification)
                <!-- Documents Summary -->
                <div class="bg-green-500/10 border border-green-500/20 rounded-2xl p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <p class="text-green-400 font-bold">✓ Nyaraka zimepakiwa</p>
                            <p class="text-gray-400 text-sm">{{ $agent->documents->count() }} document(s)</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/5 rounded-2xl p-6 border border-amber-500/30">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Hatua ya 2: Uthibitishaji wa Uso</h2>
                            <p class="text-gray-400 text-sm">Pakia picha za uso wako kutoka mwelekeo 5 tofauti</p>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 mb-6">
                        <h3 class="text-blue-400 font-bold mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Maelekezo - Liveness Detection
                        </h3>
                        <ul class="text-gray-300 text-sm space-y-1">
                            <li>• Piga picha za uso wako kutoka mwelekeo 5 tofauti</li>
                            <li>• Hakikisha mwanga ni mzuri na uso unaonekana vizuri</li>
                            <li>• Tumia camera ya mbele (selfie camera)</li>
                            <li>• Usiwe na miwani ya jua au kofia</li>
                        </ul>
                    </div>

                    <!-- Face Upload Form -->
                    <form action="{{ route('agent.face-verification.upload') }}" method="POST" enctype="multipart/form-data" id="faceForm">
                        @csrf
                        
                        <!-- Visual Upload Grid in Cross Pattern -->
                        <div class="grid grid-cols-3 gap-4 max-w-sm mx-auto mb-6">
                            <!-- Row 1: Up -->
                            <div></div>
                            <div class="text-center">
                                <label class="face-upload-box block aspect-square rounded-xl overflow-hidden border-2 border-dashed border-white/30 bg-white/5 relative cursor-pointer hover:bg-white/10" id="box_up">
                                    <input type="file" name="face_up" accept="image/*" capture="user" class="hidden" onchange="previewFace(this, 'up')">
                                    <div class="upload-icon absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-2xl mb-1">⬆️</span>
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <img class="preview-img w-full h-full object-cover" id="preview_up">
                                </label>
                                <p class="text-gray-400 text-xs mt-1">Juu</p>
                            </div>
                            <div></div>

                            <!-- Row 2: Left, Center, Right -->
                            <div class="text-center">
                                <label class="face-upload-box block aspect-square rounded-xl overflow-hidden border-2 border-dashed border-white/30 bg-white/5 relative cursor-pointer hover:bg-white/10" id="box_left">
                                    <input type="file" name="face_left" accept="image/*" capture="user" class="hidden" onchange="previewFace(this, 'left')">
                                    <div class="upload-icon absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-2xl mb-1">⬅️</span>
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <img class="preview-img w-full h-full object-cover" id="preview_left">
                                </label>
                                <p class="text-gray-400 text-xs mt-1">Kushoto</p>
                            </div>
                            <div class="text-center">
                                <label class="face-upload-box block aspect-square rounded-xl overflow-hidden border-2 border-amber-500 bg-amber-500/10 relative cursor-pointer hover:bg-amber-500/20" id="box_center">
                                    <input type="file" name="face_center" accept="image/*" capture="user" class="hidden" onchange="previewFace(this, 'center')">
                                    <div class="upload-icon absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-2xl mb-1">😊</span>
                                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <img class="preview-img w-full h-full object-cover" id="preview_center">
                                </label>
                                <p class="text-amber-400 text-xs mt-1 font-bold">Katikati</p>
                            </div>
                            <div class="text-center">
                                <label class="face-upload-box block aspect-square rounded-xl overflow-hidden border-2 border-dashed border-white/30 bg-white/5 relative cursor-pointer hover:bg-white/10" id="box_right">
                                    <input type="file" name="face_right" accept="image/*" capture="user" class="hidden" onchange="previewFace(this, 'right')">
                                    <div class="upload-icon absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-2xl mb-1">➡️</span>
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <img class="preview-img w-full h-full object-cover" id="preview_right">
                                </label>
                                <p class="text-gray-400 text-xs mt-1">Kulia</p>
                            </div>

                            <!-- Row 3: Down -->
                            <div></div>
                            <div class="text-center">
                                <label class="face-upload-box block aspect-square rounded-xl overflow-hidden border-2 border-dashed border-white/30 bg-white/5 relative cursor-pointer hover:bg-white/10" id="box_down">
                                    <input type="file" name="face_down" accept="image/*" capture="user" class="hidden" onchange="previewFace(this, 'down')">
                                    <div class="upload-icon absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-2xl mb-1">⬇️</span>
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <img class="preview-img w-full h-full object-cover" id="preview_down">
                                </label>
                                <p class="text-gray-400 text-xs mt-1">Chini</p>
                            </div>
                            <div></div>
                        </div>

                        <!-- Progress Counter -->
                        <div class="text-center mb-6">
                            <p class="text-gray-400">Picha zilizopakiwa: <span id="uploadCount" class="text-amber-500 font-bold">0</span> / 5</p>
                        </div>

                        <button type="submit" id="submitBtn" disabled class="w-full py-4 bg-gradient-to-r from-gray-600 to-gray-700 text-gray-400 font-black text-lg rounded-xl transition transform disabled:opacity-50 disabled:cursor-not-allowed">
                            Pakia picha zote 5 kwanza
                        </button>
                    </form>
                </div>

            <!-- STEP 3: Waiting for Review -->
            @else
                <!-- Documents Summary -->
                <div class="bg-green-500/10 border border-green-500/20 rounded-2xl p-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-green-400 font-bold text-sm">✓ Nyaraka zimepakiwa</p>
                    </div>
                </div>

                <!-- Face Verification Summary -->
                <div class="bg-green-500/10 border border-green-500/20 rounded-2xl p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-green-400 font-bold text-sm">✓ Picha za uso zimepakiwa</p>
                    </div>
                </div>

                <!-- Waiting State -->
                <div class="bg-amber-500/10 border border-amber-500/20 p-8 rounded-3xl text-center">
                    <div class="w-20 h-20 mx-auto bg-amber-500/20 rounded-full flex items-center justify-center mb-4 animate-pulse">
                        <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">Inasubiri Uhakiki</h2>
                    <p class="text-gray-400 mb-6">
                        Tumepokea nyaraka zako na picha za uso. Admin yetu anakagua taarifa zako. Utapokea ujumbe punde uhakiki utakapokamilika.
                    </p>
                    
                    <!-- Face Images Preview -->
                    <div class="grid grid-cols-5 gap-2 max-w-md mx-auto">
                        @foreach(['center' => 'Katikati', 'left' => 'Kushoto', 'right' => 'Kulia', 'up' => 'Juu', 'down' => 'Chini'] as $direction => $label)
                            @php $field = 'face_' . $direction; @endphp
                            <div class="text-center">
                                <div class="aspect-square rounded-lg overflow-hidden border border-green-500/50">
                                    @if($faceVerification && $faceVerification->$field)
                                        <img src="{{ url('storage/' . $faceVerification->$field) }}" alt="{{ $label }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                            <span class="text-gray-500 text-xs">-</span>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-gray-500 text-xs mt-1">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-white/10 text-center">
                <p class="text-gray-500 text-sm mb-4">
                    Uhakiki unaweza kuchukua saa 24-48. Utapokea ujumbe ukikamilika.
                </p>
                <form action="{{ route('logout') }}" method="POST">
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
    let uploadedCount = 0;
    const requiredCount = 5;

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

    function previewFace(input, direction) {
        const box = document.getElementById('box_' + direction);
        const preview = document.getElementById('preview_' + direction);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                box.classList.add('has-image');
            }
            reader.readAsDataURL(input.files[0]);
            updateUploadCount();
        }
    }

    function updateUploadCount() {
        const form = document.getElementById('faceForm');
        const inputs = form.querySelectorAll('input[type="file"]');
        let count = 0;
        
        inputs.forEach(input => {
            if (input.files && input.files[0]) {
                count++;
            }
        });
        
        uploadedCount = count;
        document.getElementById('uploadCount').textContent = count;
        
        const submitBtn = document.getElementById('submitBtn');
        if (count >= requiredCount) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Tuma kwa Uhakiki ➜';
            submitBtn.classList.remove('from-gray-600', 'to-gray-700', 'text-gray-400');
            submitBtn.classList.add('from-amber-500', 'to-orange-500', 'text-black', 'hover:from-amber-400', 'hover:to-orange-400', 'hover:scale-[1.02]');
        } else {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Pakia picha zote 5 kwanza';
            submitBtn.classList.add('from-gray-600', 'to-gray-700', 'text-gray-400');
            submitBtn.classList.remove('from-amber-500', 'to-orange-500', 'text-black', 'hover:from-amber-400', 'hover:to-orange-400', 'hover:scale-[1.02]');
        }
    }
</script>
@endpush
