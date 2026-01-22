const {
  Button,
  Modal,
  __experimentalItemGroup: ItemGroup,
  __experimentalItem: Item,
} = wp.components;
const { useState, useMemo } = wp.element;
const { __ } = wp.i18n;

export default function FormBridges({ bridges, setBridges }) {
  const [open, setOpen] = useState(false);

  const orderedBridges = useMemo(() => {
    return bridges.sort((a, b) => {
      if (isNaN(a.order)) return 1;
      if (isNaN(b.order)) return -1;
      return a.order - b.order;
    });
  }, [bridges]);

  const move = (from, to) => {
    const bridge = orderedBridges[from];

    const slicedBridges = orderedBridges
      .slice(0, from)
      .concat(orderedBridges.slice(from + 1));

    const newBridges = slicedBridges
      .slice(0, to)
      .concat(bridge)
      .concat(slicedBridges.slice(to));

    newBridges.forEach((bridge, index) => (bridge.order = index));
    setBridges(newBridges);
  };

  return (
    <>
      <Button variant="secondary" onClick={() => setOpen(true)}>
        {__("Sort bridges", "forms-bridge")}
      </Button>
      {open && (
        <Modal
          title={__("Form's bridges", "forms-bridge")}
          onRequestClose={() => setOpen(false)}
        >
          <p
            style={{
              marginTop: "-3rem",
              position: "absolute",
              zIndex: 1,
            }}
          >
            {__("Manage the chain of bridges of the form", "forms-bridge")}
          </p>
          <div
            style={{
              marginTop: "2rem",
              width: "460px",
              maxWidth: "80vw",
              minHeight: "125px",
              height: "calc(100% - 2rem)",
              display: "flex",
              flexDirection: "column",
              borderTop: "1px solid",
              borderBottom: "1px solid",
            }}
          >
            <div
              style={{
                flex: 1,
                overflowY: "auto",
                display: "flex",
                flexDirection: "column",
              }}
            >
              <ItemGroup
                size="large"
                isSeparated
                style={{ maxHeight: "calc(100% - 68px)", overflowY: "auto" }}
              >
                {orderedBridges.map((bridge, i) => (
                  <Item key={bridge.name + i}>
                    <BridgeStep
                      index={i}
                      name={bridge.name}
                      move={(direction) => move(i, i + direction)}
                      isLast={i === bridges.length - 1}
                    />
                  </Item>
                ))}
              </ItemGroup>
            </div>
          </div>
        </Modal>
      )}
    </>
  );
}

function BridgeStep({ name, index, move, isLast }) {
  return (
    <div style={{ display: "flex", alignItems: "center" }}>
      <p
        style={{
          cursor: "pointer",
          textIndent: "12px",
          padding: "10px 30px 10px 0",
          whiteSpace: "nowrap",
          margin: 0,
          overflow: "hidden",
          textOverflow: "ellipsis",
          position: "relative",
          flex: 1,
        }}
      >
        {index + 1}. <b>{name}</b>
        <span
          style={{
            display: index ? "block" : "none",
            position: "absolute",
            top: "50%",
            right: "8px",
            width: "6px",
            height: "6px",
            borderBottom: "1px solid #d1cfcf",
            borderRight: "1px solid #d1cfcf",
            transform: "translate(-50%, -50%) rotate(45deg)",
          }}
        ></span>
      </p>
      <div
        style={{
          display: "inline-flex",
          alignItems: "center",
          gap: "0.45em",
          padding: "0 0.45em 0 0.75em",
        }}
      >
        <Button
          size="compact"
          variant="secondary"
          onClick={() => move(-1)}
          style={{ width: "32px" }}
          disabled={!index}
          __next40pxDefaultSize
        >
          ⬆
        </Button>
        <Button
          size="compact"
          variant="secondary"
          onClick={() => move(+1)}
          style={{ width: "32px" }}
          disabled={isLast}
          __next40pxDefaultSize
        >
          ⬇
        </Button>
      </div>
    </div>
  );
}
