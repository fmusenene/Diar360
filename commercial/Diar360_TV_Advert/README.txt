# DIAR 360 — TV Commercial / Office Advert

A cinematic HTML5 presentation showcasing DIAR 360's full company profile. Designed for office TVs, lobbies, and digital signage.

---

## Quick Start (Office TV)

1. **Copy to USB drive:**
   - Copy the entire `Diar360` project folder to your USB drive, **OR**
   - Copy the `commercial` folder AND the `assets` folder (keeping the same structure)

2. **Run the commercial:**
   - On the office TV / computer: Insert USB → Open the `commercial` folder → Double-click `index.html`
   - The commercial will open in your default browser

3. **Fullscreen mode (recommended for TV):**
   - Press **F11** for fullscreen
   - Or right-click → Inspect is not needed; just press F11

4. **Navigation:**
   - Auto-advances every 6–10 seconds per slide
   - Use **Arrow Right** or **Space** to go to next slide
   - Use **Arrow Left** to go to previous slide
   - Click the dots at the bottom to jump to a specific slide

---

## Folder Structure for USB (portable)

Ensure this structure exists on your USB:

```
Diar360_TV_Advert/
├── index.html          ← the commercial (copy from commercial/index.html)
├── assets/
│   └── img/
│       └── construction/
│           ├── showcase-3.webp
│           ├── showcase-5.webp
│           ├── project-1.webp through project-12.webp
│           ├── CEO.webp (or CEO.png)
│           └── ... (all construction images)
└── README.txt
```

**Path fix:** If you copy only `commercial/index.html` to USB root, update the image paths inside `index.html` from `../assets/img/construction/` to `./assets/img/construction/` and place the `assets` folder in the same directory as `index.html`.

---

## Bilingual Flow (English → Arabic)

The commercial plays in two languages sequentially:
- **Slides 1–11:** English version
- **Slides 12–22:** Arabic version (العربية) with RTL layout
- After the Arabic contact slide, it loops back to the English opening

---

## Contents Included (Nothing Left Behind)

| Section | Content |
|---------|---------|
| **Opening** | DIAR 360 logo, tagline |
| **About** | Company intro, stats (16+ years, 100% success, expert engineers, global partners) |
| **Vision** | Trusted partner, Kingdom's future, sustainability |
| **Mission** | 5 mission points (client success, value-added, quality, people, exceeding expectations) |
| **Core Values** | Client satisfaction, Collaboration, Safety & Quality, Consistency & Integrity |
| **Services** | All 6: Civil & Concrete, Fit-Out, Facility Mgmt, Risk Mgmt, Landscaping, MEP |
| **Scope of Expertise** | Innovative Design, Skilled Contracting, Efficient Execution |
| **Projects** | Lamar Towers, Riyadh Metro, Al Rashed Palace, KAFD, Elegance Tower, SPA HQ, + more |
| **CEO** | Khalil Awada bio and leadership |
| **Certifications** | ISO 9001, OSHA, State Licensed, LEED |
| **Contact** | Address, email, phone, website, social |

---

## Background Music

- **Online:** Uses royalty-free music from [Mixkit](https://mixkit.co/free-stock-music/) (inspiring corporate track)
- **Offline / USB:** For playback without internet, add your own `background.mp3` to `assets/audio/` and run COPY_TO_USB
- **Controls:** Click anywhere to start audio (browser policy), then use the 🔊 button (top right) to mute/unmute
- **Volume:** Set to 35% by default

---

## Technical Notes

- **Resolution:** Optimized for 16:9 (1920×1080, 4K displays)
- **Duration:** ~2 minutes total loop
- **Browser:** Works in Chrome, Edge, Firefox, Safari
- **Offline:** Fully works offline once loaded

---

## Creating a Standalone 4K Video

To turn this into a real MP4/4K video file for TV playback without a browser:

1. Use **OBS Studio** (free): Add Browser Source → point to the HTML file → Start Recording → Output as MP4
2. Or use a professional video production service with the script provided in `PRODUCTION_SCRIPT_4K.md`
