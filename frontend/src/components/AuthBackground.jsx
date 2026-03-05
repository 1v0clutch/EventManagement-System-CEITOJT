import { useState, useEffect } from 'react';
import cvsu from '../assets/CVSU.jpg';
import cvsu1 from '../assets/cvsu1.png';

// Configuration: Background images
export const BACKGROUND_IMAGES = [
  cvsu,
  cvsu1,
];

// Configuration: Slideshow settings
const SLIDESHOW_INTERVAL = 8000; // 8 seconds per image
const FADE_DURATION = 2000; // 2 second fade transition

export default function AuthBackground() {
  const [currentImageIndex, setCurrentImageIndex] = useState(0);

  // Background slideshow effect
  useEffect(() => {
    if (BACKGROUND_IMAGES.length === 0) return;

    const interval = setInterval(() => {
      setCurrentImageIndex((prevIndex) => 
        (prevIndex + 1) % BACKGROUND_IMAGES.length
      );
    }, SLIDESHOW_INTERVAL);

    return () => clearInterval(interval);
  }, []);

  return (
    <>
      {/* Background Slideshow or Animated Gradient */}
      {BACKGROUND_IMAGES.length > 0 ? (
        // Image slideshow
        <>
          {BACKGROUND_IMAGES.map((image, index) => (
            <div
              key={index}
              className="absolute inset-0 bg-cover bg-center transition-opacity duration-2000"
              style={{
                backgroundImage: `url(${image})`,
                opacity: currentImageIndex === index ? 1 : 0,
                zIndex: 0,
              }}
            />
          ))}
          {/* Dark overlay for better text readability */}
          <div className="absolute inset-0 bg-black/40 z-0" />
        </>
      ) : (
        // Animated gradient fallback (black to white)
        <div className="absolute inset-0 z-0">
          <div className="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-600 to-gray-300 animate-gradient-shift" />
        </div>
      )}
    </>
  );
}
