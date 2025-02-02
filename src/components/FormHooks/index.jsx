// source
import CopyIcon from "../CopyIcon";

const { TabPanel } = wp.components;
const { useState, useEffect, useRef } = wp.element;
const { __ } = wp.i18n;

function TabTitle({ name, focus, setFocus, copy }) {
  return (
    <div
      style={{ position: "relative", padding: "0px 24px 0px 10px" }}
      onMouseEnter={() => setFocus(true)}
      onMouseLeave={() => setFocus(false)}
    >
      <span>{name}</span>
      {focus && <CopyIcon onClick={copy} />}
    </div>
  );
}

export default function FormHooks({ hooks, setHooks, FormHook }) {
  const [currentTab, setCurrentTab] = useState(String(hooks.length ? 0 : -1));
  const [tabFocus, setTabFocus] = useState(null);
  const tabs = hooks
    .map(({ backend, form_id, name, pipes, ...customFields }, i) => ({
      ...customFields,
      name: String(i),
      title: name,
      backend,
      form_id,
      pipes,
      icon: (
        <TabTitle
          name={name}
          focus={tabFocus === name}
          setFocus={(value) => setTabFocus(value ? name : null)}
          copy={() => copyHook(name)}
        />
      ),
    }))
    .concat([
      {
        name: "-1",
        title: __("Add Form", "forms-bridge"),
      },
    ]);

  const hookCount = useRef(hooks.length);
  useEffect(() => {
    if (hooks.length > hookCount.current) {
      setCurrentTab(String(hooks.length - 1));
    } else if (hooks.length < hookCount.current) {
      setCurrentTab(String(currentTab - 1));
    }

    return () => {
      hookCount.current = hooks.length;
    };
  }, [hooks]);

  const updateHook = (index, data) => {
    if (index === -1) index = hooks.length;
    const newHooks = hooks
      .slice(0, index)
      .concat([data])
      .concat(hooks.slice(index + 1, hooks.length));

    newHooks.forEach((hook) => {
      delete hook.title;
      delete hook.icon;
    });
    setHooks(newHooks);
  };

  const removeHook = ({ name }) => {
    const index = hooks.findIndex((h) => h.name === name);
    const newHooks = hooks.slice(0, index).concat(hooks.slice(index + 1));
    setHooks(newHooks);
  };

  const copyHook = (name) => {
    const i = hooks.findIndex((h) => h.name === name);
    const hook = hooks[i];
    const copy = {
      ...hook,
      pipes: JSON.parse(JSON.stringify(hooks.pipes || [])),
    };

    let isUnique = false;
    while (!isUnique) {
      copy.name += "-copy";
      isUnique = hooks.find((h) => h.name === copy.name) === undefined;
    }

    setHooks(hooks.concat(copy));
  };

  return (
    <div style={{ width: "100%" }}>
      <label
        className="components-base-control__label"
        style={{
          fontSize: "11px",
          textTransform: "uppercase",
          fontWeight: 500,
          marginBottom: "calc(8px)",
        }}
      >
        {__("Form Hooks", "forms-bridge")}
      </label>
      <TabPanel
        tabs={tabs}
        onSelect={setCurrentTab}
        initialTabName={currentTab}
      >
        {(hook) => {
          hook.name = hook.name >= 0 ? hooks[+hook.name].name : "add";
          return (
            <FormHook
              data={hook}
              remove={removeHook}
              update={(data) =>
                updateHook(
                  hooks.findIndex(({ name }) => name === hook.name),
                  data
                )
              }
            />
          );
        }}
      </TabPanel>
    </div>
  );
}
