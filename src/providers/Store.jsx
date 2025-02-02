const apiFetch = wp.apiFetch;
const { createContext, useContext, useEffect } = wp.element;

const StoreContext = createContext(() => {});

export default function StoreProvider({ children, setLoading }) {
  const fetchSettings = () => {
    setLoading(true);
    return apiFetch({
      path: "forms-bridge/v1/settings",
    })
      .then((settings) => {
        wpfb.emit("fetch", settings);
      })
      .finally(() => {
        setLoading(false);
      });
  };

  const fetchForms = () => {
    setLoading(true);
    return apiFetch({
      path: "forms-bridge/v1/forms",
    })
      .then((forms) => {
        wpfb.emit("forms", forms);
      })
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetchForms().then(fetchSettings);
  }, []);

  const submit = () => {
    setLoading(true);

    const settings = wpfb.bus("submit", {});
    return apiFetch({
      path: "forms-bridge/v1/settings",
      method: "POST",
      data: settings,
    }).then(fetchSettings);
  };

  return (
    <StoreContext.Provider value={submit}>{children}</StoreContext.Provider>
  );
}

export function useStoreSubmit() {
  return useContext(StoreContext);
}
