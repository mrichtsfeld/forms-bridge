const { createContext, useContext, useState, useEffect } = wp.element;

const FormsContext = createContext([]);

export default function FormsProvider({ children }) {
  const [forms, setForms] = useState([]);

  useEffect(() => {
    wpfb.on("forms", setForms);
  }, []);

  return (
    <FormsContext.Provider value={forms}>{children}</FormsContext.Provider>
  );
}

export function useForms() {
  return useContext(FormsContext);
}
