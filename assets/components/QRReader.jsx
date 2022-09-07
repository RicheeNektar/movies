import jsQR from 'jsqr';
import React, { useEffect, useRef, useState } from 'react';

const QRReader = ({ deviceId, onData, onError }) => {
  const videoRef = useRef<HTMLVideoElement>();
  const canvasRef = useRef<HTMLCanvasElement>();
  const [mediaStream, setMediaStream] = useState(null);
  const [videoTrack, setVideoTrack] = useState(null);

  useEffect(() => {
    if (mediaStream?.active) {
      mediaStream.getTracks().forEach(track => track.stop());
    }

    navigator.mediaDevices
      .getUserMedia({
        video: {
          deviceId,
          aspectRatio: 1.0,
          width: videoRef.current.clientWidth,
          frameRate: 11,
        },
      })
      .then(stream => {
        videoRef.current.srcObject = stream;
        setMediaStream(stream);
        setVideoTrack(stream.getVideoTracks().find(track => track.enabled));
      })
      .catch(onError);
  }, [deviceId]);

  useEffect(() => {
    if (!videoTrack?.enabled) {
      return;
    }

    const capture = new ImageCapture(videoTrack);

    const id = setInterval(() => {
      if (capture.track.readyState === 'live') {
        capture.grabFrame().then(bitmap => {
          const canvas = canvasRef.current;
          canvas.width = bitmap.width;
          canvas.height = bitmap.height;

          const context = canvas.getContext("2d");
          context.drawImage(bitmap, 0, 0);
          const imageData = context.getImageData(0, 0, bitmap.width, bitmap.height);

          const qr = jsQR(imageData.data, bitmap.width, bitmap.height);
          onData(qr?.data);
        });
      }
    }, 1000);

    return () => clearInterval(id);
  }, [videoTrack]);

  return (
    <>
      <video ref={videoRef} autoPlay />
      <canvas ref={canvasRef} style={{display: 'none'}} />
    </>
  );
};

export default QRReader;