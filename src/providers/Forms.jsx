import { useLoading } from "./Loading";
import { useError } from "./Error";

const { createContext, useContext, useState, useEffect, useRef } = wp.element;
const apiFetch = wp.apiFetch;
const { __ } = wp.i18n;

const FormsContext = createContext([]);

export default function FormsProvider({ children }) {
  const [, setLoading] = useLoading();
  const [, setError] = useError();
  const [forms, setForms] = useState([]);

  const fetch = useRef(() => {
    setLoading(true);

    return apiFetch({
      path: "forms-bridge/v1/forms",
    })
      .then(setForms)
      .catch(() => setError(__("Forms loading error", "forms-bridge")))
      .finally(() => setLoading(false));
  }).current;

  useEffect(() => {
    fetch();
  }, []);

  return (
    <FormsContext.Provider value={[forms, fetch]}>
      {children}
    </FormsContext.Provider>
  );
}

export function useForms() {
  return useContext(FormsContext);
}
