@if($scene === 'connection')
<svg viewBox="0 0 800 300" role="img" aria-label="{{ $ar?'رسالة تربط بين مكانين':'A message connecting two places' }}" class="editorial-art">
 <ellipse cx="400" cy="265" rx="370" ry="18" fill="#dce5dc"/>
 <path d="M115 105h155v150H115z" fill="#d9cfc0"/><path d="M102 103l90-61 91 61" fill="#526f60"/><path d="M129 124h127v84H129z" fill="#91aca3"/>
 @foreach([0,1,2] as $n)<path d="M{{ 140+$n*38 }} 136h27v31h-27zM{{ 140+$n*38 }} 176h27v24h-27z" fill="#e7eee8"/>@endforeach
 <path d="M176 211h35v44h-35z" fill="#627d70"/><path d="M540 95h150v160H540z" fill="#bdcfc3"/><path d="M528 86h174v20H528z" fill="#506d60"/><path d="M556 120h118v72H556z" fill="#eaf0e8"/><path d="M565 128h42v55h-42zm58 0h42v55h-42z" fill="#9bb5aa"/><path d="M590 205h48v50h-48z" fill="#607c6c"/>
 <path class="art-route" d="M255 137C330 50 465 50 550 130" fill="none" stroke="#849f8c" stroke-width="2" stroke-dasharray="5 7"/>
 <g class="art-message"><rect x="354" y="51" width="92" height="58" rx="12" fill="#fff" stroke="#d0ddd2"/><circle cx="373" cy="70" r="7" fill="#9bb6a1"/><path d="M390 66h39m-39 11h28m-25 17h36" stroke="#bacac0" stroke-width="4" stroke-linecap="round"/></g>
 <g fill="#9ab8a0"><path d="M53 251q-22-35 2-77 25 42-2 77M745 251q-25-40 2-85 27 45-2 85"/></g><path d="M55 255v-52m692 52v-56" stroke="#7d9684" stroke-width="3"/>
</svg>
@elseif($scene === 'cards')
<svg viewBox="0 0 320 210" class="mini-art editorial-mini" aria-hidden="true">
 <circle cx="160" cy="99" r="85" fill="#e8eee8"/>
 @if($index===0)
 <path d="M42 162c32-43 54-65 96-76m45 0c41 12 63 37 95 76" fill="none" stroke="#92ad99" stroke-width="2" stroke-dasharray="4 6"/>
 <g class="art-float"><rect x="115" y="42" width="90" height="60" rx="13" fill="#fff" stroke="#c6d6cb"/><circle cx="136" cy="61" r="8" fill="#9bb7a1"/><path d="M151 58h37m-37 10h27m-23 17h35" stroke="#bdcec1" stroke-width="4" stroke-linecap="round"/></g>
 @foreach([[66,123],[233,123]] as $pos)<g transform="translate({{ $pos[0] }} {{ $pos[1] }})"><circle cy="-14" r="13" fill="#c89e83"/><path d="M-14-17q0-18 14-17t14 18l-9-6-14 4z" fill="#374f45"/><path d="M-27 34q2-36 27-36t27 36" fill="#6c8d7a"/><path d="M-13 33v20m26-20v20" stroke="#354e43" stroke-width="8"/></g>@endforeach
 @elseif($index===1)
 <rect x="87" y="31" width="146" height="133" rx="12" fill="#faf9f3" stroke="#aabfb0" stroke-width="2"/><path d="M103 48h114v18H103z" fill="#dce8dd"/><circle cx="113" cy="57" r="3" fill="#829e8b"/><path d="M111 83h65m-65 16h97m-97 16h87m-87 16h58" stroke="#b4c6b8" stroke-width="5" stroke-linecap="round"/><g class="art-float"><circle cx="211" cy="131" r="26" fill="#597c68"/><path d="M198 131l9 9 17-20" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round"/></g>
 @else
 <path d="M60 161h200" stroke="#a9bcad" stroke-width="3"/><path d="M96 159V98q4-30 32-30t32 30v61m0 0V98q4-30 32-30t32 30v61" fill="#c7d7c9"/><path d="M120 159v-36m80 36v-36" stroke="#789685" stroke-width="3"/><g class="art-float"><circle cx="160" cy="57" r="24" fill="#fff" stroke="#cfddd1"/><path d="M147 57c0-12 13-11 13-3 0-8 13-9 13 3 0 7-13 15-13 15s-13-8-13-15z" fill="#c68f7b"/></g>
 @endif
</svg>
@elseif($scene === 'community')
<svg viewBox="0 0 520 500" role="img" aria-label="{{ $ar?'أشخاص يتشاركون التواصل':'People sharing a connection' }}">
 <circle cx="260" cy="250" r="200" fill="#304e42"/><circle cx="260" cy="250" r="155" fill="none" stroke="#789585" stroke-width="1" stroke-dasharray="5 8"/>
 <path class="art-route" d="M112 162Q260 60 410 165M110 330Q260 420 410 330" fill="none" stroke="#a8c4ae" stroke-width="2" stroke-dasharray="6 9"/>
 <g class="art-float"><rect x="183" y="170" width="154" height="122" rx="20" fill="#f2f1e9"/><circle cx="215" cy="202" r="13" fill="#8fae97"/><path d="M241 197h68m-68 14h49m-80 27h97m-97 15h75m-75 15h87" stroke="#bacbbd" stroke-width="5" stroke-linecap="round"/><circle cx="330" cy="178" r="12" fill="#c99079"/></g>
 @foreach([[100,145,'#cb9b80'],[420,145,'#b78067'],[110,340,'#d6ab8c'],[410,340,'#bf9179']] as $p)<g transform="translate({{ $p[0] }} {{ $p[1] }})"><circle r="38" fill="#dce7d9"/><circle cy="-8" r="14" fill="{{ $p[2] }}"/><path d="M-14-10q0-18 14-18t14 19l-9-7-14 3z" fill="#354b40"/><path d="M-24 25q2-22 24-22t24 22" fill="#6b8d77"/></g>@endforeach
 <g fill="#d2e0ca"><circle cx="260" cy="80" r="4"/><circle cx="260" cy="416" r="4"/><circle cx="64" cy="250" r="4"/><circle cx="455" cy="250" r="4"/></g>
</svg>
@endif
