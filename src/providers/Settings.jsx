// vendor
import React from "react";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {
  createContext,
  useContext,
  useState,
  useEffect,
  useRef,
} from "@wordpress/element";

const defaultSettings = {
  "general": {
    notification_receiver: `admin@${window.location.hostname}`,
    backends: [],
  },
  "rest-api": {
    form_hooks: [],
  },
  "rpc-api": {
    endpoint: "/jsonrpc",
    database: "crm.lead",
    user: "admin",
    password: "admin",
    form_hooks: [],
  },
};

const SettingsContext = createContext([defaultSettings, () => {}]);

export default function SettingsProvider({ children, setLoading }) {
  const persisted = useRef(true);

  const [general, setGeneral] = useState({ ...defaultSettings.general });
  const [restApi, setRestApi] = useState({ ...defaultSettings["rest-api"] });
  const [rpcApi, setRpcApi] = useState({ ...defaultSettings["rpc-api"] });

  const fetchSettings = () => {
    setLoading(true);
    return apiFetch({
      path: `${window.wpApiSettings.root}wp-bridges/v1/forms-bridge/settings`,
      headers: {
        "X-WP-Nonce": wpApiSettings.nonce,
      },
    })
      .then((settings) => {
        setGeneral(settings.general);
        setRestApi(settings["rest-api"]);
        setRpcApi(settings["rpc-api"]);
      })
      .finally(() => {
        setLoading(false);
        setTimeout(() => {
          persisted.current = true;
        }, 500);
      });
  };

  const beforeUnload = useRef((ev) => {
    if (!persisted.current) {
      ev.preventDefault();
      ev.returnValue = true;
    }
  }).current;

  useEffect(() => {
    fetchSettings();
    window.addEventListener("beforeunload", (ev) => beforeUnload(ev));
  }, []);

  useEffect(() => {
    persisted.current = false;
  }, [general, restApi, rpcApi]);

  const saveSettings = () => {
    setLoading(true);
    return apiFetch({
      path: `${window.wpApiSettings.root}wp-bridges/v1/forms-bridge/settings`,
      method: "POST",
      headers: {
        "X-WP-Nonce": wpApiSettings.nonce,
      },
      data: {
        general,
        "rest-api": restApi,
        "rpc-api": rpcApi,
      },
    }).then(fetchSettings);
  };

  return (
    <SettingsContext.Provider
      value={[
        {
          general,
          setGeneral,
          restApi,
          setRestApi,
          rpcApi,
          setRpcApi,
        },
        saveSettings,
      ]}
    >
      {children}
    </SettingsContext.Provider>
  );
}

export function useGeneral() {
  const [{ general, setGeneral }] = useContext(SettingsContext);

  const { notification_receiver: receiver, backends } = general;

  const update = ({ receiver, backends }) =>
    setGeneral({
      notification_receiver: receiver,
      backends,
    });

  return [{ receiver, backends }, update];
}

export function useRestApi() {
  const [{ restApi, setRestApi }] = useContext(SettingsContext);
  return [restApi, setRestApi];
}

export function useRpcApi() {
  const [{ rpcApi, setRpcApi }] = useContext(SettingsContext);
  return [rpcApi, setRpcApi];
}

export function useSubmitSettings() {
  const [, submit] = useContext(SettingsContext);
  return submit;
}
