import sharp from 'sharp';

async function convertLogo() {
  try {
    const inputLogo = 'public/images/PoultryProLogo.png';

    // Copy original to logo.png
    await sharp(inputLogo)
      .png()
      .toFile('public/images/logo.png');
    console.log('✓ Created public/images/logo.png');

    // Create high-res versions
    await sharp(inputLogo)
      .resize(512, 512)
      .png()
      .toFile('public/images/logo-512.png');
    console.log('✓ Created public/images/logo-512.png');

    await sharp(inputLogo)
      .resize(192, 192)
      .png()
      .toFile('public/images/logo-192.png');
    console.log('✓ Created public/images/logo-192.png');

    // Create apple-touch-icon (180x180)
    await sharp(inputLogo)
      .resize(180, 180)
      .png()
      .toFile('public/apple-touch-icon.png');
    console.log('✓ Created public/apple-touch-icon.png');

    // Create favicon sizes
    await sharp(inputLogo)
      .resize(32, 32)
      .png()
      .toFile('public/favicon-32x32.png');
    console.log('✓ Created public/favicon-32x32.png');

    await sharp(inputLogo)
      .resize(16, 16)
      .png()
      .toFile('public/favicon-16x16.png');
    console.log('✓ Created public/favicon-16x16.png');

    console.log('\n✓ All favicon and logo files generated successfully!');
  } catch (error) {
    console.error('Error converting logo:', error);
    process.exit(1);
  }
}

convertLogo();
