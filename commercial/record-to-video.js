#!/usr/bin/env node
/**
 * Record Diar 360 HTML commercial to 4K MP4 video
 * Requires: npm install playwright
 * Run: node record-to-video.js
 */

const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
const { execSync } = require('child_process');

const WIDTH = 3840;
const HEIGHT = 2160;
// Use SHORT=1 for English only (~90 sec). Full run = ~3 min (English + Arabic).
const SHORT_MODE = process.env.SHORT === '1';
const DURATION_MS = SHORT_MODE ? 95000 : 180000; // 95 sec or 3 min
const HTML_PATH = path.join(__dirname, 'index.html');
const OUTPUT_DIR = path.join(__dirname, 'output');
const WEBM_PATH = path.join(OUTPUT_DIR, 'diar360-commercial.webm');
const MP4_PATH = path.join(OUTPUT_DIR, 'Diar360_Commercial_4K.mp4');

async function main() {
  console.log('Diar 360 - Recording commercial to 4K video...');
  console.log('Resolution:', WIDTH + 'x' + HEIGHT);
  console.log('Duration:', SHORT_MODE ? '~1.5 min (English only)' : '~3 min (English + Arabic)');
  console.log('');

  if (!fs.existsSync(HTML_PATH)) {
    console.error('Error: index.html not found at', HTML_PATH);
    process.exit(1);
  }

  fs.mkdirSync(OUTPUT_DIR, { recursive: true });

  const htmlUrl = 'file:///' + HTML_PATH.replace(/\\/g, '/');
  console.log('Loading:', htmlUrl);

  const browser = await chromium.launch({
    headless: true,
    args: ['--disable-gpu', '--force-device-scale-factor=1', '--no-sandbox']
  });
  const context = await browser.newContext({
    viewport: { width: WIDTH, height: HEIGHT },
    deviceScaleFactor: 1,
    recordVideo: {
      dir: OUTPUT_DIR,
      size: { width: WIDTH, height: HEIGHT }
    },
    ignoreHTTPSErrors: true,
    javaScriptEnabled: true
  });

  const page = await context.newPage();

  await page.goto(htmlUrl, { waitUntil: 'networkidle', timeout: 60000 });
  console.log('Page loaded.');

  // Wait for fonts and images to fully render for sharp output
  await page.waitForTimeout(5000);

  await page.click('body');
  console.log('Triggered audio start.');
  await page.waitForTimeout(500);

  console.log('Recording... (please wait)');
  await page.waitForTimeout(DURATION_MS);

  await context.close();
  await browser.close();

  const video = page.video();
  if (!video) {
    console.error('Error: No video was recorded.');
    process.exit(1);
  }

  const webmPath = await video.path();
  if (webmPath && webmPath !== WEBM_PATH) {
    fs.renameSync(webmPath, WEBM_PATH);
  }

  console.log('WebM saved. Converting to MP4...');

  try {
    // High-quality 4K encoding: CRF 16, preset medium, explicit bitrate for sharp output
    execSync(`ffmpeg -y -i "${WEBM_PATH}" -c:v libx264 -preset medium -crf 16 -b:v 18M -maxrate 20M -bufsize 36M -c:a aac -b:a 256k "${MP4_PATH}"`, {
      stdio: 'inherit'
    });
    console.log('');
    console.log('Done! Video saved to:');
    console.log(MP4_PATH);
    console.log('');
    console.log('Copy this MP4 to your USB drive for TV playback.');
    if (fs.existsSync(WEBM_PATH)) fs.unlinkSync(WEBM_PATH);
  } catch (e) {
    console.log('');
    console.log('FFmpeg conversion skipped (install ffmpeg for MP4).');
    console.log('WebM video saved to:', WEBM_PATH);
    console.log('Convert manually: ffmpeg -i', WEBM_PATH, MP4_PATH);
  }
}

main().catch(err => {
  console.error(err);
  process.exit(1);
});
