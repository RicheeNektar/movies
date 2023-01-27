import React, { useEffect, useState } from "react";
import QRReader from "../components/QRReader";
import $ from 'jquery';

const LoginCodeVerify = () => {
  const [device, setDevice] = useState(null);
  const [devices, setDevices] = useState([]);
  const [error, setError] = useState(null);
  const [loginCodeId, setLoginCodeId] = useState(null);

  const updateDevices = () => {
    navigator.mediaDevices.enumerateDevices().then((devices) => {
      setDevices(
        devices
          .filter((device) => device.kind === "videoinput")
          .map((device) => ({ id: device.deviceId, name: device.label }))
      );
    });
  };

  const handleData = (data) => {
    console.log(data);

    if (!data) {
      return;
    }

    const id = Number.parseInt(data);
    if (!Number.isNaN(id)) {
      setLoginCodeId(id);
    }
  };

  useEffect(() => {
    if (loginCodeId) {
      $.post({
        url: '/api/login-code/verify',
        dataType: 'json',
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({
          id: loginCodeId,
        }),
      }).then(data => {
        if (data.ok) {
          setError({message: 'Login successful. Check your other device.'});
        }
      });
    }
  }, [loginCodeId]);

  useEffect(() => {
    if (devices) {
      setDevice(devices[0]);
    }
  }, [devices]);

  useEffect(() => {
    navigator.mediaDevices
      .getUserMedia({
        video: true,
      })
      .then(updateDevices)
      .catch(setError);
  }, []);

  return error ? (
    <p>{error.message}</p>
  ) : loginCodeId ? (
    <div className={"spinner-border"} />
  ) : (
    <>
      <select
        className={"form-select mb-3"}
        onChange={(e) =>
          setDevice(devices.find((device) => e.target.value === device.name))
        }
        value={device?.name}
      >
        {devices.map((device) => (
          <option key={device.id} id={device.id}>
            {device.name}
          </option>
        ))}
      </select>
      {device && (
        <QRReader
          onData={handleData}
          onError={console.error}
          deviceId={device.id}
        />
      )}
    </>
  );
};

export default LoginCodeVerify;
