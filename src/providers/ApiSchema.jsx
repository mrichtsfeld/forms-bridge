const apiFetch = wp.apiFetch;
const { createContext, useContext, useEffect, useRef, useState, useMemo } =
  wp.element;

const ApiSchemaContext = createContext([]);

export default function ApiSchemaProvider({ children, bridge }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(false);
  const [schema, setSchema] = useState(null);
  const [invalid, invalidate] = useState(true);

  const lastBridge = useRef(bridge);
  useEffect(() => {
    setError(false);
    invalidate(bridge !== "add" && bridge !== lastBridge.current);

    return () => {
      if (bridge !== "add") {
        lastBridge.current = bridge;
      }
    };
  }, [bridge]);

  useEffect(() => {
    if (error) setSchema(null);
  }, [error]);

  const fetch = (bridge) => {
    if (bridge === "add") return;

    setLoading(true);

    apiFetch({
      path: `forms-bridge/v1/schema/${bridge}`,
    })
      .then((schema) => {
        setSchema(schema);
        invalidate(false);
      })
      .catch(() => setError(true))
      .finally(() => setLoading(false));
  };

  const value = useMemo(() => {
    if (!loading && invalid) fetch(bridge);
    return schema;
  }, [bridge, invalid, schema]);

  return (
    <ApiSchemaContext.Provider value={value}>
      {children}
    </ApiSchemaContext.Provider>
  );
}

export function useApiFields() {
  const schema = useContext(ApiSchemaContext);
  return schema?.fields || [];
}

export function useApiContentType() {
  const schema = useContext(ApiSchemaContext);
  return schema?.content_type;
}
