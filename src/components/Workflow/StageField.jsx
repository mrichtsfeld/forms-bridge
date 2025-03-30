const { useMemo } = wp.element;

function useStyle(state, diff) {
  if (!diff) {
    return { color: "inherit", display: "inline-block" };
  }

  return {
    display: "inline-block",
    color: state.enter
      ? "#4ab866"
      : state.exit
        ? "#cc1818"
        : state.mutated
          ? "#f0b849"
          : "inherit",
  };
}

export default function WorkflowStageField({
  name,
  schema,
  showDiff,
  enter,
  mutated,
  exit,
}) {
  const style = useStyle({ enter, mutated, exit }, showDiff);

  return (
    <div style={style}>
      <strong>{name}</strong>
      <FieldSchema
        data={schema}
        showDiff={showDiff}
        enter={enter}
        exit={exit}
        mutated={mutated}
      />
    </div>
  );
}

function FieldSchema({ data, showDiff, enter, exit, mutated }) {
  // const display =
  //   data.type !== "object" && data.type !== "array" ? "inline" : "block";

  const content = useMemo(() => {
    switch (data.type) {
      case "object":
        return (
          <ObjectProperties
            data={data.properties}
            showDiff={showDiff}
            enter={enter}
            exit={exit}
            mutated={mutated}
          />
        );
      case "array":
        return (
          <ArrayItems
            data={data.items}
            showDiff={showDiff}
            enter={enter}
            exit={exit}
            mutated={mutated}
          />
        );
      default:
        return data.type;
    }
  }, [data]);

  return (
    <div
      style={{
        display: "inline",
        marginLeft: "1em",
        paddingLeft: "1em",
        borderLeft: "1px solid",
      }}
    >
      {content}
    </div>
  );
}

function ObjectProperties({ data, showDiff, enter, exit, mutated }) {
  return "object";

  return (
    <ul>
      {Object.keys(data).map((key) => (
        <li>
          <WorkflowStageField
            name={key}
            schema={data[key]}
            showDiff={showDiff}
            enter={enter}
            exit={exit}
            mutated={mutated}
          />
        </li>
      ))}
    </ul>
  );
}

function ArrayItems({ data, showDiff, enter, exit, mutated }) {
  return data.type + "[]";

  const items = Array.from(Array(data.maxItems || data.minItems || 1));

  if (data.type !== "object" && data.type !== "array") {
    return data.type + "[]";
  }

  return (
    <ol style={{ margin: 0 }}>
      {items.map(() => (
        <li>
          <WorkflowStageField
            name=""
            schema={data}
            showDiff={showDiff}
            enter={enter}
            exit={exit}
            mutated={mutated}
          />
        </li>
      ))}
    </ol>
  );
}
