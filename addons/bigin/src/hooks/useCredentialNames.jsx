import useBiginApi from "./useBiginApi";

const { useMemo } = wp.element;

export default function useCredentialNames() {
  const [{ credentials }] = useBiginApi();
  return useMemo(
    () => new Set(credentials.map(({ name }) => name)),
    [credentials]
  );
}
