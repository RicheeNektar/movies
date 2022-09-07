import QRCodeStyling from 'qr-code-styling';
import React, { useEffect, useRef } from 'react';

const QRCode = ({ value }) => {
  const ref = useRef();

  const styling = new QRCodeStyling({
    cornersDotOptions: {
      type: 'dot',
      color: '#6c757d',
    },
    cornersSquareOptions: {
      type: 'extra-rounded',
      color: '#6c757d',
    },
    dotsOptions: {
      type: 'rounded',
      color: '#0d6efd',
    },
    backgroundOptions: {
      color: '#00000000',
    },
    data: value,
  });

  useEffect(() => {
    styling.append(ref.current);
  }, [styling]);

  useEffect(() => {
    styling.update({
      data: value,
    });
  }, [styling, value]);

  return <div ref={ref} />;
};

export default QRCode;