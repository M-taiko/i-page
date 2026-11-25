<div class="world neighborhood">
 <svg viewBox="0 0 680 650" role="img" aria-label="{{ $ar?'مشهد تواصل بين الأماكن والأشخاص':'People and places connected' }}">
 <defs><linearGradient id="glass" x2="1" y2="1"><stop stop-color="#c4d5d3"/><stop offset="1" stop-color="#617f80"/></linearGradient><linearGradient id="facade" x2="0" y2="1"><stop stop-color="#f7f2e9"/><stop offset="1" stop-color="#d6cabc"/></linearGradient><filter id="shadow"><feGaussianBlur stdDeviation="12"/></filter></defs>
 <ellipse cx="340" cy="545" rx="290" ry="40" fill="#1c3931" opacity=".1" filter="url(#shadow)"/>
 <circle cx="340" cy="300" r="245" fill="#e6ebe5"/><circle cx="340" cy="300" r="218" fill="none" stroke="#b9c9bd" stroke-width="1"/>
 <g class="clouds" fill="none" stroke="#a7b9ac" stroke-width="1"><path d="M55 210h115M505 160h100M85 220h70M510 170h70"/></g>
 <g class="place hotel">
  <path d="M177 199L356 140l150 67v282H177z" fill="url(#facade)"/><path d="M356 140l150 67v282H356z" fill="#c5b8a9"/><path d="M160 205l196-68 165 69-9 13-156-60-185 61z" fill="#263f39"/>
  <path d="M190 231h152v180H190zM369 220l120 38v153H369z" fill="#79938d"/>
  @foreach([0,1,2,3] as $row) @foreach([0,1,2] as $col)<rect x="{{ 198+$col*48 }}" y="{{ 240+$row*42 }}" width="37" height="34" fill="url(#glass)" stroke="#e4e3d9" stroke-width="3"/>@endforeach @endforeach
  @foreach([0,1,2] as $row) @foreach([0,1] as $col)<rect x="{{ 380+$col*50 }}" y="{{ 270+$row*43 }}" width="39" height="33" fill="url(#glass)" stroke="#e4e3d9" stroke-width="3"/>@endforeach @endforeach
  <path d="M168 411h345v22H168z" fill="#f5eee4"/><path d="M270 433h100v57H270z" fill="#405d55"/><path d="M320 433v57M279 440h32v50m18-50h32v50" fill="none" stroke="#b9cbc0" stroke-width="3"/><path d="M190 435h62v38h-62zm199 0h90v38h-90z" fill="#809c93"/>
 </g>
 <g class="place school"><path d="M161 300l180-110 180 110v190H161z" fill="#d7c5ad"/><path d="M146 300l195-126 196 126-8 14-188-111-187 111z" fill="#475e51"/><path d="M184 320h315v93H184z" fill="#ebe1d1"/>@foreach([0,1,2,3,4] as $i)<path d="M{{ 200+$i*59 }} 335h42v56h-42z" fill="#9bb4ac" stroke="#f8f4eb" stroke-width="4"/>@endforeach<path d="M280 415h120v75H280z" fill="#516a5d"/><path d="M340 415v75" stroke="#e7e0d4" stroke-width="3"/><circle cx="341" cy="268" r="24" fill="#f3ecdf"/><path d="M341 251v18l11 7" fill="none" stroke="#60786d" stroke-width="3"/></g>
 <g class="place shop"><path d="M165 275h350v215H165z" fill="#d8d1c5"/><path d="M145 260h390v35H145z" fill="#526f64"/><path d="M160 302h360l-15 47H175z" fill="#f6f1e9"/>@foreach([0,1,2,3,4] as $i)<path d="M{{ 178+$i*68 }} 302h34l-2 47h-36z" fill="#9caf9e"/>@endforeach<rect x="190" y="365" width="110" height="90" fill="#91aca5"/><rect x="330" y="365" width="100" height="125" fill="#617d73"/><path d="M380 365v125" stroke="#e9ebe1" stroke-width="3"/><path d="M203 435l32-35 30 18 20-30" stroke="#e5ede4" stroke-width="4" fill="none"/></g>
 <g class="announcement"><rect x="272" y="75" width="128" height="72" rx="18" fill="#fff" stroke="#d7e0d8"/><circle cx="297" cy="101" r="10" fill="#769782"/><path d="M318 97h55M318 108h37M290 127h80" stroke="#b7c7bc" stroke-width="5" stroke-linecap="round"/><circle cx="395" cy="81" r="10" fill="#d29572"/></g>
 <g class="send-lines" fill="none" stroke="#8aa595" stroke-width="2" stroke-dasharray="4 8"><path d="M275 115Q95 150 102 345"/><path d="M400 115Q560 140 565 345"/></g>
 @foreach([[104,360,'#435f55','#bf8d73'],[555,365,'#a07665','#c99d84']] as $p)
 <g transform="translate({{ $p[0] }} {{ $p[1] }})"><g class="citizen" tabindex="0" role="button" aria-label="{{ $ar?'التفاعل مع المشهد':'Interact with scene' }}">
 <ellipse cx="0" cy="171" rx="38" ry="8" fill="#6a7d70" opacity=".2"/><path d="M-12 100l-8 65M12 100l13 65" stroke="#293e39" stroke-width="15" stroke-linecap="round"/><path d="M-26 62q3-24 25-24 27 1 29 24l7 49h-69z" fill="{{ $p[2] }}"/><path d="M-15 47l14 15 14-15" fill="#e4ded2"/><rect x="-7" y="23" width="14" height="20" rx="7" fill="{{ $p[3] }}"/><ellipse cy="6" rx="19" ry="25" fill="{{ $p[3] }}"/><path d="M-19 4q-5-32 16-28 24-5 24 22l-9-9-20 4z" fill="#303733"/><path d="M-5 15q5 3 10 0" fill="none" stroke="#745348" stroke-width="1.5"/>
 <g class="wave-arm"><path d="M-22 62l-24 35-12-25" fill="none" stroke="{{ $p[2] }}" stroke-width="13" stroke-linecap="round"/><circle cx="-58" cy="69" r="6" fill="{{ $p[3] }}"/></g><path d="M22 63l19 30 16-22" fill="none" stroke="{{ $p[2] }}" stroke-width="13" stroke-linecap="round"/><circle cx="57" cy="68" r="6" fill="{{ $p[3] }}"/><g class="phone"><rect x="48" y="37" width="19" height="34" rx="4" fill="#293e39"/><rect x="51" y="41" width="13" height="23" rx="2" fill="#d5e4d6"/></g>
 <g class="notification"><rect x="18" y="-54" width="70" height="35" rx="12" fill="#fff"/><circle cx="32" cy="-37" r="6" fill="#86a98e"/><path d="M44-41h29m-29 8h20" stroke="#b3c3b7" stroke-width="3" stroke-linecap="round"/></g><g class="hello"><circle cx="0" cy="-55" r="18" fill="#fff"/><path d="M-7-56q7-9 14 0-2 6-7 9-5-3-7-9" fill="#ce9985"/></g>
 </g></g>@endforeach
 <g class="trees"><path d="M45 445v85M625 445v85" stroke="#80968a" stroke-width="6"/><path d="M45 460q-45-37 0-90 45 53 0 90m580 0q-45-37 0-90 45 53 0 90" fill="#9ab7a1"/><path d="M45 450v-57m580 57v-57" stroke="#d0dfd0" stroke-width="2"/></g>
 <path d="M65 535h550" stroke="#a7b8ab" stroke-width="2"/>
 </svg>
</div>
