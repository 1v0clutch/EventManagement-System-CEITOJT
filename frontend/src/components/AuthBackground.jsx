import { useState, useEffect } from 'react';

// Configuration: Add your image URLs here
export const BACKGROUND_IMAGES = [
  // Add image URLs here, e.g.:
  // '/images/campus1.jpg',
  // '/images/campus2.jpg',
  // '/images/campus3.jpg',
];

// Configuration: Slideshow settings
const SLIDESHOW_INTERVAL = 5000; // 5 seconds per image
const FADE_DURATION = 1000; // 1 second fade transition

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
              className="absolute inset-0 bg-cover bg-center transition-opacity duration-1000"
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
