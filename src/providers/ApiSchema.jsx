import useTab from "../hooks/useTab";
import useBackends from "../hooks/useBackends";
import { useCredentials } from "../hooks/useAddon";

const apiFetch = wp.apiFetch;
const { createContext, useContext, useRef, useState, useMemo } = wp.element;

const ApiSchemaContext = createContext([]);

export default function ApiSchemaProvider({ children, bridge }) {
  const [addon] = useTab();
  const [backends] = useBackends();
  const [credentials] = useCredentials();

  const [loading, setLoading] = useState(false);
  const schemas = useRef(new Map()).current;
  const [, updates] = useState(0);

  const getSchema = () => {
    if (!bridge?.endpoint || !backend) return;

    const key = JSON.stringify({
      endpoint: bridge.endpoint,
      backend,
      credential,
    });

    return schemas.get(key);
  };

  const addSchema = (key, schema) => {
    schemas.set(key, schema);
    updates((i) => i + 1);
  };

  const fetch = (endpoint, backend, credential) => {
    setLoading(true);

    const key = JSON.stringify({ endpoint, backend, credential });
    apiFetch({
      path: `forms-bridge/v1/${addon}/backend/endpoint/schema`,
      method: "POST",
      data: { endpoint, backend, credential: credential || {} },
    })
      .then((schema) => addSchema(key, schema))
      .catch(() => addSchema(key, []))
      .finally(() => setLoading(false));
  };

  const backend = useMemo(
    () => backends.find(({ name }) => bridge?.backend === name),
    [backends, bridge]
  );

  const credential = useMemo(
    () => credentials.find(({ name }) => bridge?.credential === name),
    [credentials, bridge]
  );

  const schema = useMemo(() => {
    if (!backend || !bridge?.endpoint || loading) return;

    const value = getSchema();
    if (value === undefined) fetch(bridge.endpoint, backend, credential);
    return value;
  }, [bridge, backend, credential]);

  return (
    <ApiSchemaContext.Provider value={schema}>
      {children}
    </ApiSchemaContext.Provider>
  );
}

export function useApiFields() {
  const schema = useContext(ApiSchemaContext);
  return schema || [];
}
