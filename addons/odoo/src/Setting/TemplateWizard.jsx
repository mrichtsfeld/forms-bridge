import { useGeneral } from "../../../../src/providers/Settings";
import TemplateWizard from "../../../../src/components/Templates/Wizard";
import BackendStep from "../../../../src/components/Templates/Steps/BackendStep";
import useDatabaseNames from "../hooks/useDatabaseNames";
import DatabaseStep from "./DatabaseStep";
import BridgeStep from "./BridgeStep";
import { useTemplateConfig } from "../../../../src/providers/Templates";
import { debounce, validateUrl } from "../../../../src/lib/utils";

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

export default function OdooTemplateWizard({ integration, onDone }) {
  const [{ backends }] = useGeneral();
  const databaseNames = useDatabaseNames();

  const config = useTemplateConfig();
  const configFields = useMemo(() => config?.fields || [], [config]);
  const customFields = useMemo(() => {
    return configFields
      .filter((field) => field.ref === "#bridge/custom_fields[]")
      .map((field) => field.name);
  }, [configFields]);

  const [data, setData] = useState({});

  const [users, setUsers] = useState([]);
  const [products, setProducts] = useState([]);
  const [tags, setTags] = useState([]);
  const [teams, setTeams] = useState([]);
  const [lists, setLists] = useState([]);

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
    if (validateUrl(backend?.base_url)) {
      return true;
    }

    return data.backend?.name && validateUrl(data.backend?.base_url);
  }, [data.backend, backends]);

  const isValidDatabase = useMemo(() => {
    if (!data.database) return false;

    return (
      data.database.name &&
      data.database.user &&
      data.database.password &&
      /^[\w\-\.]+@([\w-]+\.)+[\w-]{2,}$/.test(data.database.user)
    );
  }, [data.database]);

  const fetch = useRef((model, then, database, backend) => {
    apiFetch({
      path: `forms-bridge/v1/odoo/${model}`,
      method: "POST",
      data: { backend, database },
    })
      .then(then)
      .catch(() => then([]));
  }).current;

  const fetchUsers = useRef(
    debounce((...args) => fetch("users", setUsers, ...args), 1e3)
  ).current;

  const fetchProducts = useRef(
    debounce((...args) => fetch("products", setProducts, ...args), 1e3)
  ).current;

  const fetchTags = useRef(
    debounce((...args) => fetch("tags", setTags, ...args), 1e3)
  ).current;

  const fetchTeams = useRef(
    debounce((...args) => fetch("teams", setTeams, ...args), 1e3)
  ).current;

  const fetchLists = useRef(
    debounce((...args) => fetch("lists", setLists, ...args), 1e3)
  ).current;

  useEffect(() => {
    if (!isValidBackend || !isValidDatabase) return;

    customFields.includes("user_id") && fetchUsers(data.database, data.backend);

    customFields.includes("product_id") &&
      fetchProducts(data.database, data.backend);

    customFields.includes("tag_ids") && fetchTags(data.database, data.backend);

    customFields.includes("team_id") && fetchTeams(data.database, data.backend);

    customFields.includes("list_ids") &&
      fetchLists(data.database, data.backend);
  }, [isValidDatabase, isValidBackend, customFields]);

  useEffect(() => {
    setData({
      ...data,
      bridge: {
        ...(data.bridge || {}),
        _users: users,
        _products: products,
        _tags: tags,
        _teams: teams,
        _lists: lists,
      },
    });
  }, [users, products, tags, teams, lists]);

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
