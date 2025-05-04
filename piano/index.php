<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Two Octave Piano Player</title>
  <style>
    body { font-family: sans-serif; background: #fafafa; margin:0; padding:20px; text-align:center; }
    #controls { margin-bottom:20px; }
    select, button, label { font-size:16px; margin:0 5px; }
    #controls input[type=range] { vertical-align: middle; }

    .piano { position:relative; width:560px; height:200px; margin:0 auto; background:#eee; }
    .white-key, .white-key.active {
      width:40px; height:200px; float:left; border:1px solid #000; background:#fff;
      box-sizing:border-box; cursor:pointer; transition:background 0.2s;
    }
    .white-key:hover { background:#f0f0f0; }
    .white-key.active { background:#ffeb3b; }

    .black-key, .black-key.active {
      position:absolute; top:0; z-index:2; width:25px; height:120px; background:#000;
      cursor:pointer; transition:background 0.2s;
    }
    .black-key:hover { background:#333; }
    .black-key.active { background:#ffc107; }

    .piano::after { content:""; display:table; clear:both; }
  </style>
</head>
<body>
  <div id="controls">
    <select id="songSelect">
      <option value="MaryHadALittleLamb">Mary Had a Little Lamb</option>
    </select>
    <button id="playButton">Play</button>
    <button id="stopButton" disabled>Stop</button>
    <br>
    <label>Waveform:
      <select id="waveformSelect">
        <option value="sine">Sine</option>
        <option value="square">Square</option>
        <option value="sawtooth">Sawtooth</option>
        <option value="triangle">Triangle</option>
      </select>
    </label>
    <label>Attack:<input type="range" id="attackControl" min="0.001" max="0.1" step="0.001" value="0.01"><span id="attackVal">0.01</span>s</label>
    <label>Release:<input type="range" id="releaseControl" min="0.01" max="1" step="0.01" value="0.5"><span id="releaseVal">0.5</span>s</label>
  </div>

  <div class="piano">
    <!-- Octave 1 -->
    <div class="white-key" data-note="C4"></div>
    <div class="black-key" data-note="C#4" style="left:30px;"></div>
    <div class="white-key" data-note="D4"></div>
    <div class="black-key" data-note="D#4" style="left:70px;"></div>
    <div class="white-key" data-note="E4"></div>
    <div class="white-key" data-note="F4"></div>
    <div class="black-key" data-note="F#4" style="left:150px;"></div>
    <div class="white-key" data-note="G4"></div>
    <div class="black-key" data-note="G#4" style="left:190px;"></div>
    <div class="white-key" data-note="A4"></div>
    <div class="black-key" data-note="A#4" style="left:230px;"></div>
    <div class="white-key" data-note="B4"></div>
    <!-- Octave 2 -->
    <div class="white-key" data-note="C5"></div>
    <div class="black-key" data-note="C#5" style="left:310px;"></div>
    <div class="white-key" data-note="D5"></div>
    <div class="black-key" data-note="D#5" style="left:350px;"></div>
    <div class="white-key" data-note="E5"></div>
    <div class="white-key" data-note="F5"></div>
    <div class="black-key" data-note="F#5" style="left:430px;"></div>
    <div class="white-key" data-note="G5"></div>
    <div class="black-key" data-note="G#5" style="left:470px;"></div>
    <div class="white-key" data-note="A5"></div>
    <div class="black-key" data-note="A#5" style="left:510px;"></div>
    <div class="white-key" data-note="B5"></div>
  </div>

  <script>
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const playButton = document.getElementById('playButton');
    const stopButton = document.getElementById('stopButton');
    const songSelect = document.getElementById('songSelect');
    const waveformSelect = document.getElementById('waveformSelect');
    const attackControl = document.getElementById('attackControl');
    const releaseControl = document.getElementById('releaseControl');
    const attackVal = document.getElementById('attackVal');
    const releaseVal = document.getElementById('releaseVal');
    let activeOscillators = [];
    let activeTimeouts = [];

    attackControl.oninput = () => attackVal.textContent = attackControl.value;
    releaseControl.oninput = () => releaseVal.textContent = releaseControl.value;

    const noteFrequencies = {
      'C4':261.63,'C#4':277.18,'D4':293.66,'D#4':311.13,'E4':329.63,
      'F4':349.23,'F#4':369.99,'G4':392,'G#4':415.3,'A4':440,'A#4':466.16,'B4':493.88,
      'C5':523.25,'C#5':554.37,'D5':587.33,'D#5':622.25,'E5':659.25,
      'F5':698.46,'F#5':739.99,'G5':783.99,'G#5':830.61,'A5':880,'A#5':932.33,'B5':987.77
    };

    const songs = {
      MaryHadALittleLamb: [
        { note: 'E4', d: 0.5 },{ note: 'D4', d: 0.5 },{ note: 'C4', d: 0.5 },{ note: 'D4', d: 0.5 },
        { note: 'E4', d: 0.5 },{ note: 'E4', d: 0.5 },{ note: 'E4', d: 1   },
        { note: 'D4', d: 0.5 },{ note: 'D4', d: 0.5 },{ note: 'D4', d: 1   },
        { note: 'E4', d: 0.5 },{ note: 'G4', d: 0.5 },{ note: 'G4', d: 1   },
        { note: 'E4', d: 0.5 },{ note: 'D4', d: 0.5 },{ note: 'C4', d: 0.5 },{ note: 'D4', d: 0.5 },
        { note: 'E4', d: 0.5 },{ note: 'E4', d: 0.5 },{ note: 'E4', d: 0.5 },{ note: 'E4', d: 0.5 },
        { note: 'D4', d: 0.5 },{ note: 'D4', d: 0.5 },{ note: 'E4', d: 0.5 },{ note: 'D4', d: 0.5 },{ note: 'C4', d: 1 }
      ]
    };

    function playNoteImmediate(note, duration) {
      const freq = noteFrequencies[note]; if (!freq) return;
      const osc = audioCtx.createOscillator(); const gain = audioCtx.createGain();
      osc.type = waveformSelect.value; osc.frequency.value = freq; osc.connect(gain); gain.connect(audioCtx.destination);
      activeOscillators.push(osc);

      const now = audioCtx.currentTime;
      const attackTime = parseFloat(attackControl.value);
      const releaseTime = parseFloat(releaseControl.value);
      gain.gain.setValueAtTime(0, now);
      gain.gain.linearRampToValueAtTime(1, now + attackTime);
      osc.start(now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + duration + releaseTime);
      osc.stop(now + duration + releaseTime + 0.05);
    }

    function highlightKeyImmediate(note, duration) {
      const keyEl = document.querySelector(`[data-note="${note}"]`);
      if (!keyEl) return;
      keyEl.classList.add('active');
      const rmTO = setTimeout(() => keyEl.classList.remove('active'), (duration + parseFloat(releaseControl.value)) * 1000);
      activeTimeouts.push(rmTO);
    }

    function stopSong() {
      activeOscillators.forEach(osc => { try { osc.stop(); } catch{} }); activeOscillators = [];
      activeTimeouts.forEach(to => clearTimeout(to)); activeTimeouts = [];
      document.querySelectorAll('.active').forEach(el => el.classList.remove('active'));
      playButton.disabled = false; stopButton.disabled = true;
    }

    stopButton.addEventListener('click', stopSong);
    playButton.addEventListener('click', () => {
      stopSong(); playButton.disabled = true; stopButton.disabled = false;
      let timeOffset = 0;
      const song = songs[songSelect.value] || [];
      song.forEach(({note,d}) => {
        const id = setTimeout(() => {
          playNoteImmediate(note, d); highlightKeyImmediate(note, d);
        }, timeOffset * 1000);
        activeTimeouts.push(id);
        timeOffset += d;
      });
      const endId = setTimeout(() => { playButton.disabled=false; stopButton.disabled=true; }, timeOffset * 1000);
      activeTimeouts.push(endId);
    });

    // manual key play
    document.querySelectorAll('.white-key, .black-key').forEach(key => {
      key.addEventListener('mousedown', () => {
        playNoteImmediate(key.dataset.note, 0.5);
        highlightKeyImmediate(key.dataset.note, 0.5);
      });
    });
  </script>
</body>
</html>
