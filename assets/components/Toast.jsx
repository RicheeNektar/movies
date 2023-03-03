import React, { useEffect, useRef, useState } from 'react';
import PropTypes from 'prop-types';
import moment from 'moment';
import $ from 'jquery';
import { Toast as BsToast } from 'bootstrap';

const Toast = ({ id, title, text, createAt, ackUrl }) => {
    const toastRef = useRef();
    const [toast, setToast] = useState(null);

    useEffect(() => {
        if (toast !== null) {
            toast.show();
        }
    }, [toast]);

    useEffect(() => {
        setToast(new BsToast(toastRef.current, {
            autohide: false,
        }));
    }, [toastRef]);

    const ToastCloseHandler = () => {
        $.post(`${ackUrl}/${id}`);
    };

    const key = `toast-${id}`;

    return (
        <div className="toast bg-dark" id={key} ref={toastRef}>
            <div className="toast-header bg-black bg-opacity-25">
                <svg width="20" height="20" className="rounded-2 me-2 bg-info" />
                <strong className="text-light me-auto">{title}</strong>
                <small>{moment(createAt, null, 'de').fromNow()}</small>
                <button
                    onClick={ToastCloseHandler}
                    type="button"
                    className="btn-close btn-close-white"
                    data-bs-dismiss="toast"
                    data-bs-target={key} />
            </div>
            <div className="toast-body">
                {text}
            </div>
        </div>
    );
}

Toast.propTypes = {
    id: PropTypes.number.isRequired,
    title: PropTypes.string.isRequired,
    text: PropTypes.string.isRequired,
    createAt: PropTypes.instanceOf(Date).isRequired,
    ackUrl: PropTypes.string.isRequired,
}

export default Toast;