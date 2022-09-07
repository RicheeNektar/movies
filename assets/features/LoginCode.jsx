import React, { useEffect, useState } from 'react';
import $ from 'jquery';
import QRCode from '../components/QRCode';

const LoginCode = () => {
  const [code, setCode] = useState('');

  const createLoginCode = () => {
    $.post({
      url: '/api/login-code/create',
    }).then(data => {
      setCode(data.id);
    }).catch(console.error);
  };

  useEffect(() => {
    const i = setInterval(() => {
      $.get({
        url: '/api/login-code/check?' + new URLSearchParams({
          id: code,
        }).toString(),
      }).then(data => {
        if (data.ok && data?.user) {
          clearInterval(i);

          $.post({
            url: '/login',
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            data: JSON.stringify({
              username: data.user,
              id: code,
            }),
          }).then(d => {
            if (d.ok) {
              window.location.href = new URLSearchParams(
                window.location.search
              ).get('target_path') ?? '/';
            } else if (d.ok === false) {
              createLoginCode();
            }
          })
        } else if (data.ok === false) {
          createLoginCode();
        }
      });
    }, 1000);

    return () => clearInterval(i);
  }, [code]);

  useEffect(() => {
    createLoginCode();
  }, []);

  return <QRCode value={code.toString()} />;
};

export default LoginCode;