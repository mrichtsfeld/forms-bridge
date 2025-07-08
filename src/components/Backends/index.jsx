// source
import useBackendNames from "../../hooks/useBackendNames";
import Backend from "../Backend";
import NewBackend from "../Backend/NewBackend";
import CopyIcon from "../CopyIcon";

const { TabPanel, __experimentalSpacer: Spacer } = wp.components;
const { useState } = wp.element;
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

export default function Backends({ backends, setBackends }) {
  const backendNames = useBackendNames();

  const [tabFocus, setTabFocus] = useState(null);
  const tabs = backends
    .map(({ name }, index) => ({
      index,
      name: String(index),
      title: name,
      icon: (
        <TabTitle
          name={name}
          focus={tabFocus === name}
          setFocus={(value) => setTabFocus(value ? name : null)}
          copy={() => copyBackend(name)}
        />
      ),
    }))
    .concat([
      {
        index: -1,
        name: "new",
        title: __("Add Backend", "forms-bridge"),
      },
    ]);

  const updateBackend = (index, data) => {
    if (index === -1) index = backends.length;

    const newBackends = backends
      .slice(0, index)
      .concat([data])
      .concat(backends.slice(index + 1, backends.length));

    setBackends(newBackends);
  };

  const removeBackend = ({ name }) => {
    const index = backends.findIndex((b) => b.name === name);

    const newBackends = backends
      .slice(0, index)
      .concat(backends.slice(index + 1));

    setBackends(newBackends);
  };

  const copyBackend = (name) => {
    const i = backends.findIndex((backend) => backend.name === name);
    const backend = backends[i];
    const copy = { ...backend };

    while (backendNames.has(copy.name)) {
      copy.name += "-copy";
    }

    setBackends(backends.concat(copy));
  };

  return (
    <div style={{ width: "100%" }}>
      <p>
        {__(
          "Configure your backend connexions and reuse them on your form bridges",
          "forms-bridge"
        )}
      </p>
      <Spacer paddingBottom="5px" />
      <TabPanel tabs={tabs}>
        {(tab) => {
          const backend = backends[tab.index];

          if (!backend) {
            return (
              <NewBackend add={(data) => updateBackend(tab.index, data)} />
            );
          }

          return (
            <Backend
              data={backend}
              remove={removeBackend}
              update={(newBackend) => updateBackend(tab.index, newBackend)}
            />
          );
        }}
      </TabPanel>
    </div>
  );
}
