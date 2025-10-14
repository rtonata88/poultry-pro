import sharp from 'sharp';
import { readFileSync, writeFileSync } from 'fs';

async function createSVG() {
  try {
    // Read the PNG and get its buffer
    const pngBuffer = readFileSync('public/images/PoultryProLogo.png');
    const metadata = await sharp(pngBuffer).metadata();

    // Convert PNG to base64 data URI
    const base64 = pngBuffer.toString('base64');

    // Create SVG wrapper with embedded PNG
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
  width="${metadata.width}" height="${metadata.height}" viewBox="0 0 ${metadata.width} ${metadata.height}">
  <image width="${metadata.width}" height="${metadata.height}"
    xlink:href="data:image/png;base64,${base64}"/>
</svg>`;

    writeFileSync('public/images/logo.svg', svg);
    console.log('✓ Created public/images/logo.svg');
  } catch (error) {
    console.error('Error:', error);
  }
}

createSVG();
