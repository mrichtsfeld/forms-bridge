// vendor
import React from "react";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {
  createContext,
  useContext,
  useState,
  useEffect,
} from "@wordpress/element";

const FormsContext = createContext([]);

export default function FormsProvider({ children, setLoading }) {
  const [forms, setForms] = useState([]);

  useEffect(() => {
    apiFetch({
      path: `${window.wpApiSettings.root}wp-bridges/v1/forms-bridge/forms`,
      headers: {
        "X-WP-Nonce": wpApiSettings.nonce,
      },
    })
      .then((forms) => setForms(forms))
      .finally(() => setLoading(false));
  }, []);

  return (
    <FormsContext.Provider value={forms}>{children}</FormsContext.Provider>
  );
}

export function useForms() {
  return useContext(FormsContext);
}
