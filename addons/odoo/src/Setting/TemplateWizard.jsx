import { useGeneral } from "../../../../src/providers/Settings";
import TemplateWizard from "../../../../src/components/Templates/Wizard";
import BackendStep from "../../../../src/components/Templates/Steps/BackendStep";
import useDatabaseNames from "../hooks/useDatabaseNames";
import DatabaseStep from "./DatabaseStep";
import BridgeStep from "./BridgeStep";
import { useTemplateConfig } from "../../../../src/providers/Templates";

const apiFetch = wp.apiFetch;
const { useState, useMemo, useEffect, useRef } = wp.element;

const STEPS = [
  {
    name: "database",
    step: ({ fields, data, setData }) => (
      <DatabaseStep fields={fields} data={data} setData={setData} />
    ),
    order: 0,
  },
  {
    name: "backend",
    step: ({ fields, data, setData }) => (
      <BackendStep fields={fields} data={data} setData={setData} />
    ),
    order: 5,
  },
  {
    name: "bridge",
    step: ({ fields, data, setData }) => (
      <BridgeStep fields={fields} data={data} setData={setData} />
    ),
    order: 20,
  },
];

function debounce(fn, ms = 500) {
  let timeout;

  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), ms);
  };
}

function validateUrl(url) {
  try {
    url = new URL(url);
  } catch (_) {
    return false;
  }

  return url.protocol === "http:" || url.protocol === "https:";
}

export default function OdooTemplateWizard({ integration, onDone }) {
  const config = useTemplateConfig();
  const configFields = config?.fields || [];
  const customFields = configFields
    .filter((field) => field.ref === "#bridge/custom_fields[]")
    .map((field) => field.name);

  const [{ backends }] = useGeneral();
  const databaseNames = useDatabaseNames();

  const [data, setData] = useState({});

  const [users, setUsers] = useState([]);
  const [products, setProducts] = useState([]);

  const steps = useMemo(
    () =>
      STEPS.map(({ name, step, order }) => {
        if (
          name === "backend" &&
          data.database?.name &&
          databaseNames.has(data.database.name)
        ) {
          step = null;
        }

        return { name, step, order };
      }),
    [databaseNames, data.database?.name]
  );

  useEffect(() => {
    if (data.database?.name && databaseNames.has(data.database.name)) {
      const backend = backends.find(
        ({ name }) => name === data.database.backend
      );

      setData({
        ...data,
        backend: {
          ...data.backend,
          name: backend.name,
          base_url: backend.base_url,
        },
      });
    }
  }, [data.database?.name]);

  const isValidBackend = useMemo(() => {
    if (!data.backend?.base_url) return false;
    const backend = backends.find(({ name }) => name === data.backend.name);

    if (backend && validateUrl(backend.base_url)) {
      return true;
    }

    return validateUrl(data.backend?.base_url);
  }, [data.database, data.backend, backends]);

  const isValidDatabase = useMemo(() => {
    if (!data.database) return false;

    return (
      data.database.name &&
      data.database.user &&
      data.database.password &&
      /^[\w\-\.]+@([\w-]+\.)+[\w-]{2,}$/.test(data.database.user)
    );
  }, [data.database]);

  const fetchUsers = useRef(
    debounce((database, backend) => {
      backend = {
        ...backend,
        headers: [
          {
            name: "Content-Type",
            value: "application/json",
          },
        ],
      };

      apiFetch({
        path: "forms-bridge/v1/odoo/users",
        method: "POST",
        data: { backend, database },
      })
        .then(setUsers)
        .catch(() => setUsers([]));
    }, 500)
  ).current;

  const fetchProducts = useRef(
    debounce((database, backend) => {
      backend = {
        ...backend,
        headers: [
          {
            name: "Content-Type",
            value: "application/json",
          },
        ],
      };

      apiFetch({
        path: "forms-bridge/v1/odoo/products",
        method: "POST",
        data: { backend, database },
      })
        .then(setProducts)
        .catch(() => setProducts([]));
    })
  ).current;

  useEffect(() => {
    if (!isValidBackend || !isValidDatabase) return;

    customFields.includes("user_id") && fetchUsers(data.database, data.backend);
    customFields.includes("product_id") &&
      fetchProducts(data.database, data.backend);
  }, [data.database, isValidDatabase, data.backend, isValidBackend, config]);

  useEffect(() => {
    setData({
      ...data,
      bridge: {
        ...(data.bridge || {}),
        _users: users,
        _products: products,
      },
    });
  }, [users]);

  return (
    <TemplateWizard
      integration={integration}
      steps={steps}
      data={data}
      setData={setData}
      onDone={onDone}
    />
  );
}
