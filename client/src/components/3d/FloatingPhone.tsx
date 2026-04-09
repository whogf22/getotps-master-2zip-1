import { useRef, useMemo, Suspense } from "react";
import { Canvas, useFrame } from "@react-three/fiber";
import { Float, RoundedBox } from "@react-three/drei";
import * as THREE from "three";

function PhoneBody() {
  const group = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (group.current) {
      group.current.rotation.y = Math.sin(state.clock.elapsedTime * 0.4) * 0.3;
      group.current.rotation.x = Math.sin(state.clock.elapsedTime * 0.3) * 0.1;
    }
  });

  return (
    <Float speed={1.2} rotationIntensity={0.2} floatIntensity={0.8}>
      <group ref={group}>
        {/* Phone body */}
        <RoundedBox args={[1.4, 2.6, 0.12]} radius={0.12} smoothness={4}>
          <meshStandardMaterial
            color="#0f172a"
            metalness={0.9}
            roughness={0.05}
            envMapIntensity={1}
          />
        </RoundedBox>

        {/* Screen glow */}
        <RoundedBox args={[1.2, 2.3, 0.01]} radius={0.08} smoothness={4} position={[0, 0, 0.07]}>
          <meshStandardMaterial
            color="#0ea5e9"
            emissive="#0ea5e9"
            emissiveIntensity={0.3}
            roughness={0.1}
            metalness={0.1}
          />
        </RoundedBox>

        {/* OTP code display */}
        <mesh position={[0, 0.1, 0.08]}>
          <planeGeometry args={[1.0, 0.5]} />
          <meshStandardMaterial
            color="#1e293b"
            emissive="#0f766e"
            emissiveIntensity={0.5}
            roughness={0.5}
          />
        </mesh>

        {/* OTP digit blocks */}
        {[0, 1, 2, 3, 4, 5].map((i) => (
          <mesh key={i} position={[-0.38 + i * 0.16, 0.1, 0.09]}>
            <boxGeometry args={[0.12, 0.3, 0.01]} />
            <meshStandardMaterial
              color="#22d3ee"
              emissive="#22d3ee"
              emissiveIntensity={0.8}
            />
          </mesh>
        ))}

        {/* Camera notch */}
        <mesh position={[0, 1.2, 0.07]}>
          <cylinderGeometry args={[0.05, 0.05, 0.01, 16]} />
          <meshStandardMaterial color="#1e293b" />
        </mesh>

        {/* Home button */}
        <mesh position={[0, -1.1, 0.07]}>
          <cylinderGeometry args={[0.08, 0.08, 0.01, 32]} />
          <meshStandardMaterial color="#1e293b" metalness={0.8} roughness={0.2} />
        </mesh>

        {/* Shield icon */}
        <mesh position={[0, -0.4, 0.09]} rotation={[0, 0, 0]}>
          <cylinderGeometry args={[0.18, 0.18, 0.01, 5]} />
          <meshStandardMaterial
            color="#0891b2"
            emissive="#0891b2"
            emissiveIntensity={0.6}
            transparent
            opacity={0.9}
          />
        </mesh>
      </group>
    </Float>
  );
}

function FloatingCode({ position, value, color }: { position: [number, number, number]; value: string; color: string }) {
  const mesh = useRef<THREE.Mesh>(null);

  useFrame((state) => {
    if (mesh.current) {
      mesh.current.position.y = position[1] + Math.sin(state.clock.elapsedTime * 0.8 + position[0]) * 0.15;
      (mesh.current.material as THREE.MeshStandardMaterial).emissiveIntensity =
        0.4 + Math.sin(state.clock.elapsedTime * 1.5) * 0.2;
    }
  });

  return (
    <mesh ref={mesh} position={position}>
      <boxGeometry args={[0.6, 0.25, 0.04]} />
      <meshStandardMaterial
        color={color}
        emissive={color}
        emissiveIntensity={0.4}
        metalness={0.5}
        roughness={0.3}
        transparent
        opacity={0.85}
      />
    </mesh>
  );
}

function Particles3D() {
  const mesh = useRef<THREE.Points>(null);
  const count = 400;

  const positions = useMemo(() => {
    const pos = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      pos[i * 3] = (Math.random() - 0.5) * 8;
      pos[i * 3 + 1] = (Math.random() - 0.5) * 8;
      pos[i * 3 + 2] = (Math.random() - 0.5) * 4;
    }
    return pos;
  }, []);

  useFrame((state) => {
    if (mesh.current) {
      mesh.current.rotation.y = state.clock.elapsedTime * 0.03;
    }
  });

  return (
    <points ref={mesh}>
      <bufferGeometry>
        <bufferAttribute attach="attributes-position" array={positions} count={positions.length / 3} itemSize={3} />
      </bufferGeometry>
      <pointsMaterial size={0.02} color="#22d3ee" transparent opacity={0.5} sizeAttenuation />
    </points>
  );
}

export function FloatingPhone() {
  return (
    <div className="w-full h-full">
      <Canvas
        camera={{ position: [0, 0, 5], fov: 50 }}
        gl={{ antialias: true, alpha: true }}
        dpr={[1, 1.5]}
      >
        <ambientLight intensity={0.4} />
        <pointLight position={[4, 4, 4]} intensity={3} color="#22d3ee" />
        <pointLight position={[-4, -2, 2]} intensity={2} color="#818cf8" />
        <pointLight position={[0, 0, 3]} intensity={1.5} color="#0ea5e9" />

        <Suspense fallback={null}>
          <PhoneBody />

          <FloatingCode position={[-2.2, 0.8, 0]} value="524831" color="#0891b2" />
          <FloatingCode position={[2, 1.2, -0.5]} value="719203" color="#7c3aed" />
          <FloatingCode position={[-2, -0.8, 0.5]} value="384920" color="#059669" />
          <FloatingCode position={[2.2, -0.5, 0]} value="651047" color="#0891b2" />

          <Particles3D />
        </Suspense>
      </Canvas>
    </div>
  );
}
