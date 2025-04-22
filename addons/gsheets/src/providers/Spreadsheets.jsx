const apiFetch = wp.apiFetch;
const { createContext, useContext, useEffect, useState } = wp.element;

const SpreadsheetsContext = createContext([]);

export default function SpreadsheetsProvider({ children }) {
  const [spreadsheets, setSpreadsheets] = useState([]);

  useEffect(() => {
    apiFetch({
      path: "forms-bridge/v1/spreadsheets",
    }).then((spreadsheets) => {
      setSpreadsheets(spreadsheets);
    });
  }, []);

  return (
    <SpreadsheetsContext.Provider value={spreadsheets}>
      {children}
    </SpreadsheetsContext.Provider>
  );
}

export function useSpreadsheets() {
  return useContext(SpreadsheetsContext);
}
